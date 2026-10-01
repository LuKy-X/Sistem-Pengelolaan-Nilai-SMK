<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan dan Minuman', 'description' => 'Olahan makanan dan minuman hasil produksi siswa.'],
            ['name' => 'Kriya Tekstil', 'description' => 'Kain, busana, dan produk anyaman khas sekolah.'],
            ['name' => 'Komputer dan Aplikasi', 'description' => 'Jasa desain, pemrograman, dan dukungan teknologi.'],
            ['name' => 'Otomotif', 'description' => 'Jasa perbaikan dan perawatan kendaraan.'],
            ['name' => 'Manufaktur', 'description' => 'Jasa fabrikasi, pemesinan, dan pengelasan komponen.'],
            ['name' => 'Kerajinan dan Souvenir', 'description' => 'Souvenir dan kerajinan buatan tangan siswa.'],
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
