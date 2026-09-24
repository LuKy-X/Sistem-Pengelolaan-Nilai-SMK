<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('MAPEL-???'));

        return [
            'code' => $code,
            'name' => fake()->words(2, true),
            'category' => 'MUATAN_KEJURUAN',
            'department_id' => null,
            'is_active' => true,
        ];
    }
}
