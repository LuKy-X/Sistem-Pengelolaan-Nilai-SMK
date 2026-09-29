<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use Illuminate\Database\Seeder;

class AdmissionSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2026/2027')->first()
            ?? AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderByDesc('start_date')->first();

        if ($academicYear === null) {
            return;
        }

        $period = AdmissionPeriod::firstOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'PPDB Tahun Pelajaran 2026/2027',
            ],
            [
                'registration_start' => '2026-06-01',
                'registration_end' => '2026-07-15',
                'description' => 'Penerimaan Peserta Didik Baru gelombang pertama.',
                'status' => 'OPEN',
            ],
        );

        $scheduleItems = [
            [
                'title' => 'Pendaftaran Online',
                'description' => 'Peserta mendaftar melalui formulir online di situs sekolah.',
                'start_date' => '2026-06-01',
                'end_date' => '2026-07-15',
                'step_number' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => 'Verifikasi Berkas',
                'description' => 'Panitia memeriksa kelengkapan berkas pendaftaran.',
                'start_date' => '2026-06-20',
                'end_date' => '2026-06-28',
                'step_number' => 2,
                'sort_order' => 2,
            ],
            [
                'title' => 'Tes Kemampuan Akademik',
                'description' => 'Tes membaca, matematika, dan bahasa daya.',
                'start_date' => '2026-07-05',
                'end_date' => '2026-07-06',
                'step_number' => 3,
                'sort_order' => 3,
            ],
            [
                'title' => 'Wawancara Orang Tua',
                'description' => 'Wawancara singkat bersama orang tua peserta.',
                'start_date' => '2026-07-08',
                'end_date' => '2026-07-10',
                'step_number' => 4,
                'sort_order' => 4,
            ],
            [
                'title' => 'Pengumuman Hasil',
                'description' => 'Pengumuman hasil seleksi dan daftar ulang.',
                'start_date' => '2026-07-12',
                'end_date' => '2026-07-12',
                'step_number' => 5,
                'sort_order' => 5,
            ],
        ];

        foreach ($scheduleItems as $item) {
            $period->scheduleItems()->firstOrCreate(
                ['title' => $item['title']],
                $item,
            );
        }

        $paths = [
            [
                'name' => 'Jalur Prestasi',
                'slug' => 'jalur-prestasi',
                'description' => 'Untuk calon peserta dengan prestasi akademik atau non akademik.',
                'quota' => 40,
                'sort_order' => 1,
            ],
            [
                'name' => 'Jalur Reguler',
                'slug' => 'jalur-reguler',
                'description' => 'Jalur pendaftaran umum melalui tes kemampuan akademik.',
                'quota' => 200,
                'sort_order' => 2,
            ],
            [
                'name' => 'Jalur Afirmasi',
                'slug' => 'jalur-afirmasi',
                'description' => 'Jalur bagi calon peserta dari keluarga prasejahtera.',
                'quota' => 30,
                'sort_order' => 3,
            ],
        ];

        foreach ($paths as $path) {
            $period->paths()->firstOrCreate(
                ['slug' => $path['slug']],
                $path + ['is_active' => true],
            );
        }

        $requirements = [
            ['title' => 'Fotokopi Ijazah atau Surat Keterangan Lulus', 'sort_order' => 1],
            ['title' => 'Fotokopi Rapor semester 1 sampai 5', 'sort_order' => 2],
            ['title' => 'Pas foto berwarna ukuran 3x4 sebanyak 3 lembar', 'sort_order' => 3],
            ['title' => 'Fotokopi KTP Orang Tua dan Kartu Keluarga', 'sort_order' => 4],
            ['title' => 'Surat Pernyataan Orang Tua', 'sort_order' => 5],
        ];

        foreach ($requirements as $requirement) {
            $period->requirements()->firstOrCreate(
                ['title' => $requirement['title']],
                $requirement + ['description' => null],
            );
        }

        $feeItems = [
            [
                'name' => 'Formulir Pendaftaran',
                'amount' => 100000,
                'is_free' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Biaya Akademik dan PengBulatan',
                'amount' => 2500000,
                'is_free' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Biaya Seragam dan Perlengkapan',
                'amount' => 750000,
                'is_free' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Jalur Afirmasi (Diskon)',
                'amount' => 0,
                'is_free' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($feeItems as $item) {
            $period->feeItems()->firstOrCreate(
                ['name' => $item['name']],
                $item + ['description' => null],
            );
        }
    }
}
