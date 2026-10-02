<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        // Kode sengaja di luar rentang X / XI / XII / XIII karena GradeLevelSeeder
        // sudah memakai ketiganya. Kalau factory memakai kode yang sama, panggilan
        // kedua akan bentrok UNIQUE (grade_levels.code).
        $code = 'X'.fake()->unique()->numberBetween(10, 9999);

        return [
            'code' => $code,
            'name' => 'Tingkat '.$code,
        ];
    }
}
