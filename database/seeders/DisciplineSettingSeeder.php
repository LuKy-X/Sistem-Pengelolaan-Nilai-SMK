<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\DisciplineSetting;
use Illuminate\Database\Seeder;

class DisciplineSettingSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first();

        if (! $year) {
            return;
        }

        DisciplineSetting::firstOrCreate(
            ['academic_year_id' => $year->id],
            [
                'initial_points' => 100,
                'minimum_points' => 0,
                'warning_threshold' => 75,
                'sp1_threshold' => 50,
                'sp2_threshold' => 30,
                'sp3_threshold' => 10,
            ]
        );
    }
}
