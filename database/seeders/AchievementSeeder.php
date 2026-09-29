<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'category' => 'lomba-kompetensi',
                'title' => 'Juara 2 Lomba Kompetensi Teknik Kendaraan Ringan',
                'scope' => 'SISWA',
                'level' => 'Nasional',
                'achievement_date' => '2026-08-15',
                'organizer' => 'Direktorat Pembinaan SMK',
                'rank' => 'Juara 2',
                'description' => 'Lomba overhaul mesin dan diagnose sistem EFI.',
                'is_featured' => true,
            ],
            [
                'category' => 'olimpiade-sains',
                'title' => 'Juara 1 Olimpiade Sains Nasional bidang Komputer',
                'scope' => 'SISWA',
                'level' => 'Nasional',
                'achievement_date' => '2026-07-28',
                'organizer' => 'Direktorat Jenderal Pendidikan Dasar dan Menengah',
                'rank' => 'Juara 1',
                'description' => 'Karya aplikasi dan algoritma karya siswa RPL.',
                'is_featured' => true,
            ],
            [
                'category' => 'seni-dan-budaya',
                'title' => 'Juara 1 Festival Seni dan Budaya Tingkat Kabupaten',
                'scope' => 'SISWA',
                'level' => 'Kabupaten',
                'achievement_date' => '2026-06-20',
                'organizer' => 'Dinas Pendidikan Kabupaten',
                'rank' => 'Juara 1',
                'description' => 'Karya batik dan busana karya siswa.',
                'is_featured' => true,
            ],
            [
                'category' => 'olahraga',
                'title' => 'Juara 3 Turnamen Futsal Tingkat Provinsi',
                'scope' => 'SISWA',
                'level' => 'Provinsi',
                'achievement_date' => '2026-05-17',
                'organizer' => 'KONI Provinsi',
                'rank' => 'Juara 3',
                'description' => 'Atlet futsal sekolah mengikuti turnamen antar sekolah.',
                'is_featured' => false,
            ],
            [
                'category' => 'penghargaan-sekolah',
                'title' => 'Penghargaan Sekolah Berprestasi Tingkat Provinsi',
                'scope' => 'SEKOLAH',
                'level' => 'Provinsi',
                'achievement_date' => '2026-04-30',
                'organizer' => 'Dinas Pendidikan Provinsi',
                'rank' => 'Peringkat 3',
                'description' => 'Penghargaan atas peningkatan mutu pembelajaran sekolah.',
                'is_featured' => true,
            ],
            [
                'category' => 'lomba-kompetensi',
                'title' => 'Juara 1 Lomba Desain Produk Kreatif',
                'scope' => 'SISWA',
                'level' => 'Provinsi',
                'achievement_date' => '2026-03-22',
                'organizer' => 'Asosiasi Pengusaha Ritel Indonesia',
                'rank' => 'Juara 1',
                'description' => 'Produk kemasan karya siswa DKV.',
                'is_featured' => false,
            ],
            [
                'category' => 'olimpiade-sains',
                'title' => 'Juara 2 Olimpiade Matematika Tingkat Kabupaten',
                'scope' => 'SISWA',
                'level' => 'Kabupaten',
                'achievement_date' => '2026-02-14',
                'organizer' => 'MGMP Matematika Kabupaten',
                'rank' => 'Juara 2',
                'description' => 'Perolehan nilai tertinggi pada tiga mata pelajaran.',
                'is_featured' => false,
            ],
            [
                'category' => 'seni-dan-budaya',
                'title' => 'Medali Emas Pameran Karya Tekstil',
                'scope' => 'SISWA',
                'level' => 'Nasional',
                'achievement_date' => '2026-01-24',
                'organizer' => 'Kementerian Pendidikan dan Kebudayaan',
                'rank' => 'Medali Emas',
                'description' => 'Karya tenun dan rajut karya siswa.',
                'is_featured' => false,
            ],
        ];

        foreach ($achievements as $achievement) {
            $category = AchievementCategory::where('slug', $achievement['category'])->first();

            if ($category === null) {
                continue;
            }

            Achievement::firstOrCreate(
                ['title' => $achievement['title']],
                [
                    'achievement_category_id' => $category->id,
                    'scope' => $achievement['scope'],
                    'level' => $achievement['level'],
                    'achievement_date' => $achievement['achievement_date'],
                    'organizer' => $achievement['organizer'],
                    'rank' => $achievement['rank'],
                    'description' => $achievement['description'],
                    'is_featured' => $achievement['is_featured'],
                ],
            );
        }
    }
}
