<?php

namespace Database\Factories;

use App\Models\ClassEnrollment;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassEnrollment>
 */
class ClassEnrollmentFactory extends Factory
{
    protected $model = ClassEnrollment::class;

    public function definition(): array
    {
        return [
            'class_id' => SchoolClass::factory(),
            'student_id' => StudentProfile::factory(),
            'start_date' => '2024-07-15',
            'end_date' => null,
            'status' => 'ACTIVE',
        ];
    }
}
