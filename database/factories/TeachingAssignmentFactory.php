<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeachingAssignmentFactory extends Factory
{
    protected $model = TeachingAssignment::class;

    public function definition(): array
    {
        return [
            'teacher_id' => TeacherProfile::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => SchoolClass::factory(),
            'semester_id' => Semester::factory(),
            'weekly_hours' => 2,
            'is_active' => true,
        ];
    }
}
