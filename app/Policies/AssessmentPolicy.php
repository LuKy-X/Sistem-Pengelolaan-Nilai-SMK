<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    /**
     * Determine whether the user can view the assessment.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher check: Must be the teaching assignment teacher
        if ($user->isTeacher() && $user->teacherProfile) {
            return $assessment->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        // Student check: Must be enrolled in the teaching assignment's class
        if ($user->isStudent() && $user->studentProfile) {
            return $user->studentProfile->classEnrollments()
                ->where('class_id', $assessment->teachingAssignment->class_id)
                ->where('status', 'ACTIVE')
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create an assessment for a teaching assignment.
     */
    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the assessment.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $assessment->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the assessment.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $assessment->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }
}
