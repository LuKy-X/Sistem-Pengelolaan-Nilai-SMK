<?php

namespace Database\Seeders;

use App\Models\CareerService;
use Illuminate\Database\Seeder;

class CareerServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Pendampingan PKL',
                'slug' => 'pendampingan-pkl',
                'description' => 'Pembekalan, penempatan, dan monitoring praktik kerja lapangan.',
                'icon' => 'briefcase',
                'sort_order' => 1,
            ],
            [
                'title' => 'Bursa Kerja Siswa',
                'slug' => 'bursa-kerja-siswa',
                'description' => 'Informasi lowongan kerja dan jejak alumni setiap bulan.',
                'icon' => 'briefcase',
                'sort_order' => 2,
            ],
            [
                'title' => 'Pelatihan Karier',
                'slug' => 'pelatihan-karier',
                'description' => 'Pelatihan pembuatan CV, surat lamaran, dan wawancara kerja.',
                'icon' => 'user',
                'sort_order' => 3,
            ],
            [
                'title' => 'Bimbingan Konseling Karier',
                'slug' => 'bimbingan-konseling-karier',
                'description' => 'Konseling pilihan jurusan, kuliah, atau bekerja bagi siswa kelas XII.',
                'icon' => 'user',
                'sort_order' => 4,
            ],
            [
                'title' => 'Jejaring Alumni',
                'slug' => 'jejaring-alumni',
                'description' => 'Tukar pengalaman dan informasi lowongan dari alumni.',
                'icon' => 'briefcase',
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $service) {
            CareerService::firstOrCreate(
                ['slug' => $service['slug']],
                $service + ['is_active' => true],
            );
        }
    }
}
