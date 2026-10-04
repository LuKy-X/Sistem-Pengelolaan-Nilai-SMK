<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Berita', 'slug' => 'berita'],
            ['name' => 'Kerja Sama Industri', 'slug' => 'kerja-sama-industri'],
            ['name' => 'Prestasi', 'slug' => 'prestasi'],
            ['name' => 'PPDB', 'slug' => 'ppdb'],
            ['name' => 'Kegiatan Sekolah', 'slug' => 'kegiatan-sekolah'],
            ['name' => 'Karier dan Alumni', 'slug' => 'karier-dan-alumni'],
            ['name' => 'Pengumuman', 'slug' => 'pengumuman'],
        ];

        foreach ($categories as $category) {
            ArticleCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
