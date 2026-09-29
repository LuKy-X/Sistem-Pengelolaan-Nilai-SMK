<?php

namespace Database\Seeders;

use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class AchievementCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Lomba Kompetensi', 'slug' => 'lomba-kompetensi'],
            ['name' => 'Olimpiade Sains', 'slug' => 'olimpiade-sains'],
            ['name' => 'Seni dan Budaya', 'slug' => 'seni-dan-budaya'],
            ['name' => 'Olahraga', 'slug' => 'olahraga'],
            ['name' => 'Penghargaan Sekolah', 'slug' => 'penghargaan-sekolah'],
        ];

        foreach ($categories as $category) {
            AchievementCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
