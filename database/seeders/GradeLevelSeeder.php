<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['code' => 'X', 'name' => 'Tingkat X (Sepuluh)'],
            ['code' => 'XI', 'name' => 'Tingkat XI (Sebelas)'],
            ['code' => 'XII', 'name' => 'Tingkat XII (Dua Belas)'],
        ];

        foreach ($levels as $level) {
            GradeLevel::firstOrCreate(['code' => $level['code']], $level);
        }
    }
}
