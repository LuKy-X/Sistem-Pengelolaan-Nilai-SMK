<?php

namespace Database\Seeders;

use App\Models\LessonPeriod;
use Illuminate\Database\Seeder;

class LessonPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            ['period_number' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'label' => 'Jam Ke-1 (07.00 - 07.45)'],
            ['period_number' => 2, 'start_time' => '07:45:00', 'end_time' => '08:30:00', 'label' => 'Jam Ke-2 (07.45 - 08.30)'],
            ['period_number' => 3, 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'label' => 'Jam Ke-3 (08.30 - 09.15)'],
            ['period_number' => 4, 'start_time' => '09:30:00', 'end_time' => '10:15:00', 'label' => 'Jam Ke-4 (09.30 - 10.15)'],
            ['period_number' => 5, 'start_time' => '10:15:00', 'end_time' => '11:00:00', 'label' => 'Jam Ke-5 (10.15 - 11.00)'],
            ['period_number' => 6, 'start_time' => '11:00:00', 'end_time' => '11:45:00', 'label' => 'Jam Ke-6 (11.00 - 11.45)'],
            ['period_number' => 7, 'start_time' => '12:30:00', 'end_time' => '13:15:00', 'label' => 'Jam Ke-7 (12.30 - 13.15)'],
            ['period_number' => 8, 'start_time' => '13:15:00', 'end_time' => '14:00:00', 'label' => 'Jam Ke-8 (13.15 - 14.00)'],
        ];

        foreach ($periods as $p) {
            LessonPeriod::firstOrCreate(['period_number' => $p['period_number']], $p);
        }
    }
}
