<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\StudentProduct;
use Illuminate\Database\Seeder;

class StudentProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'category' => 'Makanan dan Minuman',
                'department' => 'TPK',
                'name' => 'Keripik Pisang Tiramisu',
                'slug' => 'keripik-pisang',
                'description' => 'Keripik Pisang khas sekolah dengan tiga varian rasa.',
                'price' => 15000,
            ],
            [
                'category' => 'Makanan dan Minuman',
                'department' => 'TPK',
                'name' => 'Kue Lapis Legit',
                'slug' => 'kue-lapis-legit',
                'description' => 'Kue lapis legit produksi siswa dengan kemasan box.',
                'price' => 45000,
            ],
            [
                'category' => 'Kriya Tekstil',
                'department' => 'TPK',
                'name' => 'Kain Tenun Motif Khas',
                'slug' => 'kain-tenun-motif-khas',
                'description' => 'Kain tenun handmade bermotif khas daerah.',
                'price' => 185000,
            ],
            [
                'category' => 'Kriya Tekstil',
                'department' => 'TPK',
                'name' => 'Tas Rajut Handmade',
                'slug' => 'tas-handmade-rajut',
                'description' => 'Tas rajut handmade dengan bahan wol premium.',
                'price' => 95000,
            ],
            [
                'category' => 'Komputer dan Aplikasi',
                'department' => 'RPL',
                'name' => 'Jasa Pembuatan Website Sekolah',
                'slug' => 'jasa-pembuatan-website-sekolah',
                'description' => 'Pembuatan website sekolah responsif beserta domain dan hosting.',
                'price' => 750000,
            ],
            [
                'category' => 'Komputer dan Aplikasi',
                'department' => 'RPL',
                'name' => 'Jasa Desain Grafis',
                'slug' => 'jasa-desain-grafis',
                'description' => 'Desain poster, banner, dan identitas visual untuk umkm.',
                'price' => 250000,
            ],
            [
                'category' => 'Otomotif',
                'department' => 'TKR',
                'name' => 'Jasa Servis Ringan Motor',
                'slug' => 'jasa-servis-ringan-motor',
                'description' => 'Servis ringan, tune up, dan penggantian oli motor.',
                'price' => 60000,
            ],
            [
                'category' => 'Manufaktur',
                'department' => 'TPM',
                'name' => 'Jasa Fabrikasi Presisi',
                'slug' => 'jasa-fabrikasi-presisi',
                'description' => 'Pembubutan, frais, dan pengelasan komponen sesuai gambar teknik.',
                'price' => 450000,
            ],
            [
                'category' => 'Kerajinan dan Souvenir',
                'department' => 'DKV',
                'name' => 'Souvenir Custom Sekolah',
                'slug' => 'souvenir-custom-sekolah',
                'description' => 'Souvenir custom berbentuk logo sekolah dengan desain karya siswa.',
                'price' => 35000,
            ],
        ];

        foreach ($products as $product) {
            $category = ProductCategory::where('name', $product['category'])->first();

            if ($category === null) {
                continue;
            }

            $department = Department::where('code', $product['department'])->first();

            StudentProduct::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $category->id,
                    'department_id' => $department?->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'contact' => '081234567890',
                    'status' => 'AVAILABLE',
                ],
            );
        }
    }
}
