<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * General subjects only. Department-scoped (productive) subjects live in
     * DepartmentDetailSeeder, keyed by `departments.code`.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }
    }
}
