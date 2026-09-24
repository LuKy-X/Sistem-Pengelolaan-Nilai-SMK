<?php

namespace App\Policies;

use App\Models\Gradebook;
use App\Models\User;

class GradebookPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Gradebook $gradebook): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $gradebook->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Gradebook $gradebook): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $gradebook->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Gradebook $gradebook): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $gradebook->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }
}
