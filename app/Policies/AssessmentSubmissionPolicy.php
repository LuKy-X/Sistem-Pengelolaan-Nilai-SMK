<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\User;

class AssessmentSubmissionPolicy
{
    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, AssessmentSubmission $submission): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Student owner
        if ($user->isStudent() && $user->studentProfile) {
            return $submission->student_id === $user->studentProfile->id;
        }

        // Teacher of the assignment
        if ($user->isTeacher() && $user->teacherProfile) {
            return $submission->assessment->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the student can submit for the assessment.
     */
    public function create(User $user, Assessment $assessment): bool
    {
        if (! $user->isStudent() || ! $user->studentProfile) {
            return false;
        }

        // Must be active and enrolled in the class
        return $user->studentProfile->classEnrollments()
            ->where('class_id', $assessment->teachingAssignment->class_id)
            ->where('status', 'ACTIVE')
            ->exists();
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, AssessmentSubmission $submission): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            return $submission->assessment->teachingAssignment->teacher_id === $user->teacherProfile->id;
        }

        return false;
    }
}
