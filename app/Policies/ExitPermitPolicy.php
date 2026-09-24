<?php

namespace App\Policies;

use App\Models\ExitPermit;
use App\Models\User;
use App\Services\DepartmentApprovalService;

class ExitPermitPolicy
{
    protected DepartmentApprovalService $departmentApprovalService;

    public function __construct(DepartmentApprovalService $departmentApprovalService)
    {
        $this->departmentApprovalService = $departmentApprovalService;
    }

    /**
     * Determine whether the user can create an exit permit.
     */
    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can view the permit.
     */
    public function view(User $user, ExitPermit $permit): bool
    {
        if ($user->isAdmin() || $user->isSecurity()) {
            return true;
        }

        if ($permit->user_id === $user->id) {
            return true;
        }

        if ($permit->approver_id === $user->id) {
            return true;
        }

        if ($this->departmentApprovalService->isUserHodOfDepartment($user, $permit->department)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the draft permit.
     */
    public function update(User $user, ExitPermit $permit): bool
    {
        return $permit->user_id === $user->id && $permit->isDraft();
    }

    /**
     * Determine whether the user can cancel the permit.
     */
    public function cancel(User $user, ExitPermit $permit): bool
    {
        return $permit->canBeCancelledBy($user) || ($user->isAdmin() && in_array($permit->status, [ExitPermit::STATUS_DRAFT, ExitPermit::STATUS_PENDING_APPROVAL], true));
    }

    /**
     * Determine whether the user can approve the permit.
     * Prioritizes the authoritative snapshotted approver_id.
     */
    public function approve(User $user, ExitPermit $permit): bool
    {
        if ($permit->user_id === $user->id) {
            return false; // Cannot approve own SIK under any circumstance!
        }

        if (!$permit->isPending()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $permit->approver_id === $user->id;
    }

    /**
     * Determine whether the user can reject the permit.
     * Prioritizes the authoritative snapshotted approver_id.
     */
    public function reject(User $user, ExitPermit $permit): bool
    {
        if ($permit->user_id === $user->id) {
            return false; // Cannot reject own SIK
        }

        if (!$permit->isPending()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $permit->approver_id === $user->id;
    }

    /**
     * Determine whether the user can revoke the approved permit.
     */
    public function revoke(User $user, ExitPermit $permit): bool
    {
        if (!$permit->canBeRevoked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($permit->approver_id === $user->id) {
            return true;
        }

        return $this->departmentApprovalService->isUserHodOfDepartment($user, $permit->department);
    }

    /**
     * Determine whether the user can manually close an unreturned permit.
     */
    public function closeManually(User $user, ExitPermit $permit): bool
    {
        if (!$permit->canBeClosedManually()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($permit->approver_id === $user->id) {
            return true;
        }

        return $this->departmentApprovalService->isUserHodOfDepartment($user, $permit->department);
    }

    /**
     * Determine whether the user can perform security scanner gate actions.
     */
    public function scan(User $user): bool
    {
        return $user->isSecurity();
    }
}
