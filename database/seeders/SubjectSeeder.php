<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $rpl = Department::where('code', 'RPL')->first();

        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'category' => 'MUATAN_NASIONAL', 'department_id' => null],
            ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek', 'category' => 'MUATAN_KEJURUAN', 'department_id' => $rpl?->id],
            ['code' => 'PWPB', 'name' => 'Pemrograman Web dan Perangkat Bergerak', 'category' => 'MUATAN_KEJURUAN', 'department_id' => $rpl?->id],
            ['code' => 'BD', 'name' => 'Basis Data', 'category' => 'MUATAN_KEJURUAN', 'department_id' => $rpl?->id],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }
    }
}
