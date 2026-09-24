<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('???'));

        return [
            'code' => $code,
            'name' => 'Jurusan '.$code,
            'short_name' => $code,
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
