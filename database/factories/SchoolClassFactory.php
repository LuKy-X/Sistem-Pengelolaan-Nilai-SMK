<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $code = fake()->unique()->lexify('X-???-1');

        return [
            'academic_year_id' => AcademicYear::factory(),
            'department_id' => Department::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'homeroom_teacher_id' => null,
            'code' => $code,
            'name' => 'Kelas '.$code,
            'is_active' => true,
        ];
    }
}
