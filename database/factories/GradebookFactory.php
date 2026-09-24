<?php

namespace Database\Factories;

use App\Models\Gradebook;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradebookFactory extends Factory
{
    protected $model = Gradebook::class;

    public function definition(): array
    {
        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'name' => 'Buku Nilai '.fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
