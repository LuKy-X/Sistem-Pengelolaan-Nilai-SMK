<?php

namespace Database\Factories;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\GradebookColumn;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        return [
            'gradebook_column_id' => GradebookColumn::factory(),
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'type' => AssessmentType::Task,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'instructions' => fake()->paragraph(),
            'due_at' => now()->addDays(7),
            'submission_required' => true,
            'rubric_id' => null,
            'created_by' => TeacherProfile::factory(),
            'published_at' => now(),
            'status' => AssessmentStatus::Published,
        ];
    }
}
