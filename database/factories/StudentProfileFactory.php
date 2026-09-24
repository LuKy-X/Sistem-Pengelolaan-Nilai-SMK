<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nis' => fake()->unique()->numerify('#####'),
            'nisn' => fake()->unique()->numerify('00########'),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['MALE', 'FEMALE']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->date('Y-m-d', '2008-01-01'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'entry_date' => '2024-07-15',
            'status' => 'ACTIVE',
        ];
    }
}
