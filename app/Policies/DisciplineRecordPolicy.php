<?php

namespace App\Policies;

use App\Models\DisciplineRecord;
use App\Models\User;

class DisciplineRecordPolicy
{
    /**
     * Determine whether the user can view any discipline records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCounselor();
    }

    /**
     * Determine whether the user can create records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCounselor();
    }

    /**
     * Determine whether the user can update the record.
     */
    public function update(User $user, DisciplineRecord $record): bool
    {
        return $user->isAdmin() || $user->isCounselor();
    }

    /**
     * Determine whether the user can delete the record.
     */
    public function delete(User $user, DisciplineRecord $record): bool
    {
        return $user->isAdmin() || $user->isCounselor();
    }
}
