<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    protected $model = TeacherProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nip' => fake()->unique()->numerify('19##########'),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['MALE', 'FEMALE']),
            'phone' => fake()->phoneNumber(),
            'photo' => null,
            'status' => 'ACTIVE',
        ];
    }
}
