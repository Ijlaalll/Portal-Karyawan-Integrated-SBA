<?php

namespace App\Policies;

use App\Models\AtkRequest;
use App\Models\User;

class AtkRequestPolicy
{
    /**
     * Determine whether the user can view the request detail.
     */
    public function view(User $user, AtkRequest $request): bool
    {
        // Admin or GA (with module access) can view any request
        if ($user->isAdmin() || ($user->isGa() && $user->hasModuleAccess('atk'))) {
            return true;
        }

        // Requester can view their own request
        return $user->id === $request->user_id;
    }

    /**
     * Determine whether the user can cancel the request.
     */
    public function cancel(User $user, AtkRequest $request): bool
    {
        // Only requester can cancel
        if ($user->id !== $request->user_id) {
            return false;
        }

        // Only while status is 'diajukan' (submitted)
        return $request->status === 'diajukan';
    }
}
