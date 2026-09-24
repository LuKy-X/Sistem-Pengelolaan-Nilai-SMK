<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentSubmissionFactory extends Factory
{
    protected $model = AssessmentSubmission::class;

    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'student_id' => StudentProfile::factory(),
            'submitted_at' => now(),
            'status' => SubmissionStatus::Submitted,
            'content' => fake()->paragraph(),
            'late_minutes' => 0,
            'teacher_feedback' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
