<?php

namespace App\Policies;

use App\Models\ExitPermit;
use App\Models\User;

class ExitPermitPolicy
{
    /**
     * Determine whether the user can view the permit.
     */
    public function view(User $user, ExitPermit $permit): bool
    {
        if ($user->isAdmin() || $user->isCounselor()) {
            return true;
        }

        if ($user->isStudent() && $user->studentProfile) {
            return $permit->student_id === $user->studentProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the student can request an exit permit.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    /**
     * Determine whether the user can approve/reject the permit.
     */
    public function process(User $user, ExitPermit $permit): bool
    {
        return $user->isAdmin() || $user->isCounselor();
    }

    /**
     * Determine whether the student can appeal the late return.
     */
    public function appeal(User $user, ExitPermit $permit): bool
    {
        if (! $user->isStudent() || ! $user->studentProfile) {
            return false;
        }

        return $permit->student_id === $user->studentProfile->id;
    }
}
