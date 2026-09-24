<?php

namespace Database\Factories;

use App\Models\Rubric;
use App\Models\TeacherProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class RubricFactory extends Factory
{
    protected $model = Rubric::class;

    public function definition(): array
    {
        return [
            'name' => 'Rubrik Penilaian '.fake()->word(),
            'description' => fake()->sentence(),
            'created_by' => TeacherProfile::factory(),
            'status' => 'ACTIVE',
        ];
    }
}
