<?php

namespace Database\Seeders;

use App\Models\SiteStatistic;
use Illuminate\Database\Seeder;

class SiteStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $statistics = [
            [
                'section' => 'HERO',
                'key' => 'siswa_aktif',
                'label' => 'Siswa Aktif',
                'value' => '1.248',
                'description' => 'Siswa aktif pada tahun pelajaran berjalan.',
                'sort_order' => 1,
            ],
            [
                'section' => 'HERO',
                'key' => 'program_keahlian',
                'label' => 'Program Keahlian',
                'value' => '5',
                'description' => 'Program keahlian yang dibuka setiap tahun.',
                'sort_order' => 2,
            ],
            [
                'section' => 'HERO',
                'key' => 'mitra_industri',
                'label' => 'Mitra Industri',
                'value' => '60+',
                'description' => 'Perusahaan mitra kerja sama praktik kerja lapangan.',
                'sort_order' => 3,
            ],
            [
                'section' => 'HERO',
                'key' => 'lulusan_terserap',
                'label' => 'Lulusan Terserap',
                'value' => '96%',
                'description' => 'Lulusan yang bekerja, berwirausaha, atau melanjutkan kuliah.',
                'sort_order' => 4,
            ],
        ];

        foreach ($statistics as $statistic) {
            SiteStatistic::firstOrCreate(['key' => $statistic['key']], $statistic + ['is_active' => true]);
        }
    }
}
