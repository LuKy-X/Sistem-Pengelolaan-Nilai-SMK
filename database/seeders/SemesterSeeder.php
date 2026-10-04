<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first();

        if (! $year) {
            return;
        }

        Semester::updateOrCreate(
            ['academic_year_id' => $year->id, 'semester_number' => 1],
            [
                'name' => 'Ganjil',
                'start_date' => '2026-07-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
            ]
        );

        Semester::updateOrCreate(
            ['academic_year_id' => $year->id, 'semester_number' => 2],
            [
                'name' => 'Genap',
                'start_date' => '2027-01-01',
                'end_date' => '2027-06-30',
                'is_active' => false,
            ]
        );
    }
}
