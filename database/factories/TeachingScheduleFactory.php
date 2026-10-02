<?php

namespace Database\Factories;

use App\Models\LessonPeriod;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Jadwal mingguan guru. Data ini sengaja tidak dibuat oleh seeder proyek:
 * menambahkannya ke DatabaseSeeder akan mengubah hasil seed yang dipakai
 * seluruh modul, sehingga factory ini dipakai dari test saja.
 */
class TeachingScheduleFactory extends Factory
{
    protected $model = TeachingSchedule::class;

    public function definition(): array
    {
        $startPeriod = LessonPeriod::query()->orderBy('period_number')->first();

        if ($startPeriod === null) {
            throw new \LogicException(
                'Jadwal pelajaran belum ada. Jalankan LessonPeriodSeeder sebelum memakai TeachingScheduleFactory.'
            );
        }

        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'day_of_week' => fake()->numberBetween(1, 6),
            'start_period_id' => $startPeriod->id,
            'end_period_id' => $startPeriod->id,
            'room' => 'Ruang '.fake()->randomElement(['101', '102', '201', 'Lab Komputer']),
        ];
    }
}
