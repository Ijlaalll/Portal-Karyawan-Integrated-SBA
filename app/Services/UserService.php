<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'joined_at' => $data['joined_at'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
            ]);

            // Assign modules if user is not admin
            if ($user->role !== 'admin' && isset($data['modules'])) {
                foreach ($data['modules'] as $module) {
                    $user->modules()->create([
                        'module' => $module,
                    ]);
                }
            }

            // Send Account Created Email Notification
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendAccountCreated($user, $data['password']);

            return $user;
        });
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'joined_at' => $data['joined_at'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
            ]);

            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            // Sync module access permissions
            $user->modules()->delete();

            if ($user->role !== 'admin' && isset($data['modules'])) {
                foreach ($data['modules'] as $module) {
                    $user->modules()->create([
                        'module' => $module,
                    ]);
                }
            }

            return $user;
        });
    }

    /**
     * Import users in bulk from a CSV file.
     *
     * @param string $filePath
     * @param bool $updateExisting
     * @return array{created: int, updated: int, skipped: int, errors: array<string>}
     */
    public function importUsersFromCsv(string $filePath, bool $updateExisting = true): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Tidak dapat membuka file CSV.');
        }

        // Auto-detect delimiter (, or ;)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (strpos($firstLine, ';') !== false && substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false && substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (!$rawHeader) {
            fclose($handle);
            throw new \RuntimeException('File CSV kosong atau format tidak valid.');
        }

        // Clean UTF-8 BOM if present on first header
        $rawHeader[0] = preg_replace('/[\x{FEFF}\x{EF}\x{BB}\x{BF}]/u', '', $rawHeader[0]);

        // Normalize header names to lowercase trimmed strings
        $headerMap = [];
        foreach ($rawHeader as $idx => $col) {
            $cleanCol = strtolower(trim($col));
            if (in_array($cleanCol, ['name', 'nama', 'nama lengkap', 'nama_lengkap'])) {
                $headerMap['name'] = $idx;
            } elseif (in_array($cleanCol, ['email', 'e-mail', 'alamat email'])) {
                $headerMap['email'] = $idx;
            } elseif (in_array($cleanCol, ['employee_id', 'nip', 'no pegawai', 'no_pegawai', 'nik', 'id_pegawai'])) {
                $headerMap['employee_id'] = $idx;
            } elseif (in_array($cleanCol, ['department', 'departemen', 'unit kerja', 'unit_kerja', 'divisi'])) {
                $headerMap['department'] = $idx;
            } elseif (in_array($cleanCol, ['position', 'jabatan', 'posisi'])) {
                $headerMap['position'] = $idx;
            } elseif (in_array($cleanCol, ['role', 'peran'])) {
                $headerMap['role'] = $idx;
            } elseif (in_array($cleanCol, ['password', 'kata sandi', 'kata_sandi'])) {
                $headerMap['password'] = $idx;
            }
        }

        if (!isset($headerMap['name']) && !isset($headerMap['email'])) {
            fclose($handle);
            throw new \RuntimeException('Format CSV tidak valid. Pastikan terdapat kolom nama dan email.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        $baselineModules = ['meeting_room', 'atk', 'sik', 'vehicle'];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                if (empty(array_filter($row))) {
                    continue;
                }

                $name = isset($headerMap['name']) && isset($row[$headerMap['name']]) ? trim($row[$headerMap['name']]) : null;
                $email = isset($headerMap['email']) && isset($row[$headerMap['email']]) ? strtolower(trim($row[$headerMap['email']])) : null;
                $employeeId = isset($headerMap['employee_id']) && isset($row[$headerMap['employee_id']]) ? trim($row[$headerMap['employee_id']]) : null;
                $department = isset($headerMap['department']) && isset($row[$headerMap['department']]) ? trim($row[$headerMap['department']]) : null;
                $position = isset($headerMap['position']) && isset($row[$headerMap['position']]) ? trim($row[$headerMap['position']]) : null;
                $role = isset($headerMap['role']) && isset($row[$headerMap['role']]) ? strtolower(trim($row[$headerMap['role']])) : 'user';
                $password = isset($headerMap['password']) && !empty(trim($row[$headerMap['password']])) ? trim($row[$headerMap['password']]) : 'Sba12345!';

                if (!$name) {
                    $errors[] = "Baris {$rowNumber}: Nama wajib diisi.";
                    $skipped++;
                    continue;
                }

                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Baris {$rowNumber}: Format email tidak valid untuk '{$name}' ({$email}).";
                    $skipped++;
                    continue;
                }

                if (!in_array($role, ['user', 'admin', 'approver', 'security', 'head_office_driver'])) {
                    $role = 'user';
                }

                // Check existing user by email or employee_id
                $user = User::where('email', $email)
                    ->when($employeeId, function ($query, $nip) {
                        return $query->orWhere('employee_id', $nip);
                    })
                    ->first();

                if ($user) {
                    if ($updateExisting) {
                        $user->name = $name;
                        if ($department) $user->department = $department;
                        if ($position) $user->position = $position;
                        if ($employeeId) $user->employee_id = $employeeId;
                        $user->save();

                        // Provision baseline modules if user currently has none
                        if ($user->role === 'user' && $user->modules()->count() === 0) {
                            foreach ($baselineModules as $mod) {
                                \App\Models\UserModule::firstOrCreate([
                                    'user_id' => $user->id,
                                    'module' => $mod,
                                ]);
                            }
                        }

                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $newUser = User::create([
                        'name' => $name,
                        'email' => $email,
                        'employee_id' => $employeeId,
                        'department' => $department,
                        'position' => $position,
                        'role' => $role,
                        'password' => Hash::make($password),
                        'status' => 'active',
                    ]);

                    if ($role === 'user') {
                        foreach ($baselineModules as $mod) {
                            \App\Models\UserModule::create([
                                'user_id' => $newUser->id,
                                'module' => $mod,
                            ]);
                        }
                    }

                    $created++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
