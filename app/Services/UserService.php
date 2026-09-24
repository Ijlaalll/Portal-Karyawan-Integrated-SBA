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
}
