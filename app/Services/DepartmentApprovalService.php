<?php

namespace App\Services;

use App\Models\DepartmentApproval;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DepartmentApprovalService
{
    /**
     * Get department approval configuration by name.
     */
    public function getDepartmentConfig(string $department): ?DepartmentApproval
    {
        return DepartmentApproval::with(['hod', 'hodApprover', 'replacementApprovers'])
            ->where('department_name', $department)
            ->first();
    }

    /**
     * Check if user is configured as HOD for a specific department.
     */
    public function isUserHodOfDepartment(User $user, string $department): bool
    {
        return DepartmentApproval::where('department_name', $department)
            ->where('hod_user_id', $user->id)
            ->exists();
    }

    /**
     * Check if user is configured as HOD for any department.
     */
    public function isUserHod(User $user): bool
    {
        return DepartmentApproval::where('hod_user_id', $user->id)->exists();
    }

    /**
     * Check if user is configured as Atasan/Approver HOD for any department.
     */
    public function isUserHodApprover(User $user): bool
    {
        return DepartmentApproval::where('hod_approver_user_id', $user->id)->exists();
    }

    /**
     * Check if user is an authorized approver (HOD, Atasan HOD, Replacement, or has pending assigned SIK).
     */
    public function isUserAuthorizedApprover(User $user): bool
    {
        return DepartmentApproval::where('hod_user_id', $user->id)
            ->orWhere('hod_approver_user_id', $user->id)
            ->orWhereHas('replacementApprovers', fn($q) => $q->where('users.id', $user->id))
            ->exists() ||
            \App\Models\ExitPermit::where('approver_id', $user->id)
                ->where('status', \App\Models\ExitPermit::STATUS_PENDING_APPROVAL)
                ->exists();
    }

    /**
     * Check if user is the authoritative approver for a specific SIK.
     * Respects snapshot immutability: only the snapshotted approver_id (or Admin) can approve.
     */
    public function isUserAuthorizedApproverForPermit(User $user, \App\Models\ExitPermit $permit): bool
    {
        if ($permit->user_id === $user->id) {
            return false; // Absolute self-approval protection!
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $permit->approver_id === $user->id;
    }

    /**
     * Get departments where user is HOD or Approver.
     */
    public function getManagedDepartments(User $user): array
    {
        if ($user->isAdmin()) {
            return DepartmentApproval::pluck('department_name')->toArray();
        }

        return DepartmentApproval::where('hod_user_id', $user->id)
            ->orWhere('hod_approver_user_id', $user->id)
            ->orWhereHas('replacementApprovers', fn($q) => $q->where('users.id', $user->id))
            ->pluck('department_name')
            ->toArray();
    }

    /**
     * Get eligible approval options for an applicant: default approver and authorized replacement approvers.
     * Self-approval protection is applied: the applicant is excluded from replacement options.
     *
     * @throws ValidationException
     */
    public function getEligibleApproversForApplicant(User $user): array
    {
        $defaultApprover = $this->getApproverForApplicant($user);

        $config = $this->getDepartmentConfig($user->department);
        $replacementApprovers = collect();

        if ($config) {
            // Exclude applicant (cannot approve self) and default approver
            $replacementApprovers = $config->replacementApprovers()
                ->where('users.id', '!=', $user->id)
                ->where('users.id', '!=', $defaultApprover->id)
                ->where('users.status', 'active')
                ->get();
        }

        return [
            'default_approver' => $defaultApprover,
            'replacement_approvers' => $replacementApprovers,
        ];
    }

    /**
     * Resolve the designated default approver for an applicant.
     *
     * @throws ValidationException
     */
    public function getApproverForApplicant(User $user): User
    {
        if (empty($user->department)) {
            throw ValidationException::withMessages([
                'department' => 'Departemen pengguna belum diatur di sistem. Silakan hubungi Administrator.',
            ]);
        }

        $config = $this->getDepartmentConfig($user->department);

        if (!$config) {
            throw ValidationException::withMessages([
                'department' => "Konfigurasi approval untuk departemen '{$user->department}' belum diatur di sistem. Silakan hubungi Administrator.",
            ]);
        }

        // Case A: Applicant is HOD of their department
        if ($config->hod_user_id === $user->id) {
            if (!$config->hod_approver_user_id || !$config->hodApprover) {
                throw ValidationException::withMessages([
                    'approver' => "Approver Atasan HOD untuk departemen '{$user->department}' belum dikonfigurasi. Silakan hubungi Administrator.",
                ]);
            }

            if ($config->hodApprover->status !== 'active') {
                throw ValidationException::withMessages([
                    'approver' => "Approver Atasan HOD untuk departemen '{$user->department}' ({$config->hodApprover->name}) saat ini tidak aktif. Silakan hubungi Administrator.",
                ]);
            }

            if ($config->hod_approver_user_id === $user->id) {
                throw ValidationException::withMessages([
                    'approver' => "Konfigurasi approval tidak valid: HOD tidak boleh menjadi atasan dirinya sendiri. Silakan hubungi Administrator.",
                ]);
            }

            return $config->hodApprover;
        }

        // Case B: Applicant is regular staff in the department
        if (!$config->hod_user_id || !$config->hod) {
            throw ValidationException::withMessages([
                'approver' => "HOD untuk departemen '{$user->department}' belum dikonfigurasi. Silakan hubungi Administrator.",
            ]);
        }

        if ($config->hod->status !== 'active') {
            throw ValidationException::withMessages([
                'approver' => "HOD untuk departemen '{$user->department}' ({$config->hod->name}) saat ini tidak aktif. Silakan hubungi Administrator.",
            ]);
        }

        if ($config->hod_user_id === $user->id) {
            throw ValidationException::withMessages([
                'approver' => 'Pemohon tidak boleh menjadi approver untuk pengajuan SIK miliknya sendiri.',
            ]);
        }

        return $config->hod;
    }

    /**
     * Create or update department approval configuration.
     *
     * @throws ValidationException
     */
    public function saveDepartmentConfig(string $department, ?int $hodUserId, ?int $hodApproverUserId, ?string $notes = null, array $replacementApproverIds = []): DepartmentApproval
    {
        if ($hodUserId && $hodApproverUserId && $hodUserId === $hodApproverUserId) {
            throw ValidationException::withMessages([
                'hod_approver_user_id' => 'HOD dan Approver Atasan HOD tidak boleh merupakan pengguna yang sama.',
            ]);
        }

        if ($hodUserId && in_array($hodUserId, $replacementApproverIds)) {
            throw ValidationException::withMessages([
                'replacement_approver_ids' => 'HOD Utama tidak perlu dipilih kembali sebagai Approver Pengganti.',
            ]);
        }

        $config = DepartmentApproval::updateOrCreate(
            ['department_name' => trim($department)],
            [
                'hod_user_id' => $hodUserId,
                'hod_approver_user_id' => $hodApproverUserId,
                'notes' => $notes,
            ]
        );

        $config->replacementApprovers()->sync($replacementApproverIds);

        return $config->load('replacementApprovers');
    }
}
