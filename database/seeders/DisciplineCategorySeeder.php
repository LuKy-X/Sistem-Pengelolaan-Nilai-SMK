<?php

namespace Database\Seeders;

use App\Enums\DisciplineCategoryType;
use App\Models\DisciplineCategory;
use Illuminate\Database\Seeder;

class DisciplineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Violations (Negatif)
            ['name' => 'Terlambat Masuk Sekolah (< 15 menit)', 'type' => DisciplineCategoryType::Violation, 'default_points' => -5, 'description' => 'Siswa datang setelah bel masuk berbunyi.'],
            ['name' => 'Terlambat Masuk Sekolah (> 15 menit)', 'type' => DisciplineCategoryType::Violation, 'default_points' => -10, 'description' => 'Siswa terlambat lebih dari 15 menit.'],
            ['name' => 'Atribut Seragam Tidak Lengkap', 'type' => DisciplineCategoryType::Violation, 'default_points' => -5, 'description' => 'Tidak memakai dasi, sabuk, kaos kaki standar, atau topi saat upacara.'],
            ['name' => 'Meninggalkan Kelas Tanpa Izin', 'type' => DisciplineCategoryType::Violation, 'default_points' => -15, 'description' => 'Keluar dari lingkungan kelas saat jam KBM berlangsung tanpa surat izin.'],
            ['name' => 'Merokok / Membawa Rokok/Vape', 'type' => DisciplineCategoryType::Violation, 'default_points' => -25, 'description' => 'Membawa, menyimpan, atau menghisap rokok/vape di lingkungan sekolah.'],
            ['name' => 'Terlibat Perkelahian / Tawuran', 'type' => DisciplineCategoryType::Violation, 'default_points' => -50, 'description' => 'Terlibat adu fisik atau perkelahian di dalam atau luar sekolah.'],

            // Rewards (Positif)
            ['name' => 'Juara 1 Lomba Tingkat Kabupaten / Kota', 'type' => DisciplineCategoryType::Reward, 'default_points' => 20, 'description' => 'Meraih juara 1 kompetisi akademik atau non-akademik.'],
            ['name' => 'Juara 1/2/3 Lomba Tingkat Provinsi / Nasional', 'type' => DisciplineCategoryType::Reward, 'default_points' => 35, 'description' => 'Meraih prestasi tingkat provinsi atau nasional.'],
            ['name' => 'Pengurus Aktif OSIS / MPK / Ekstrakurikuler', 'type' => DisciplineCategoryType::Reward, 'default_points' => 10, 'description' => 'Menjadi panitia atau pengurus organisasi siswa.'],
            ['name' => 'Aksi Teladan Kejujuran (Mengembalikan Barang Hilang)', 'type' => DisciplineCategoryType::Reward, 'default_points' => 10, 'description' => 'Menemukan dan mengembalikan uang/barang berharga.'],
        ];

        foreach ($categories as $cat) {
            DisciplineCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
