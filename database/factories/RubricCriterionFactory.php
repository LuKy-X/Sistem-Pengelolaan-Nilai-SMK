<?php

namespace Database\Factories;

use App\Models\Rubric;
use App\Models\RubricCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

class RubricCriterionFactory extends Factory
{
    protected $model = RubricCriterion::class;

    public function definition(): array
    {
        return [
            'rubric_id' => Rubric::factory(),
            'criterion' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'max_points' => 50.00,
            'sort_order' => 1,
        ];
    }
}
