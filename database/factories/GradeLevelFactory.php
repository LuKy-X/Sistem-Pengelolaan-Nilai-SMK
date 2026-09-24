<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        $code = fake()->unique()->randomElement(['X', 'XI', 'XII', 'XIII']);

        return [
            'code' => $code,
            'name' => 'Tingkat '.$code,
        ];
    }
}
