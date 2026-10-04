<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'category' => 'berita',
                'title' => 'Rekapitulasi Realisasi Penggunaan Dana BOS Tahap 1 Tahun 2026',
                'slug' => 'rekapitulasi-realisasi-penggunaan-dana-bos-tahap-1-tahun-2026',
                'author' => 'Admin Sekolah',
                'published_at' => '2026-08-12 00:00:00',
                'excerpt' => 'Rekapitulasi realisasi penggunaan Dana BOS Tahap 1 Tahun 2026 SMK Negeri 2 Karanganyar.',
                'content' => 'SMK Negeri 2 Karanganyar menyampaikan rekapitulasi realisasi penggunaan Dana BOS Tahap 1 Tahun 2026 sebagai bentuk keterbukaan informasi sekolah.',
            ],
            [
                'category' => 'berita',
                'title' => 'Capaian Prestasi Nasional Antarkan SMK Negeri 2 Karanganyar ke Peringkat 15 SMK Terbaik Jawa Tengah',
                'slug' => 'capaian-prestasi-nasional-antarkan-smk-negeri-2-karanganyar-ke-peringkat-15-smk-terbaik-jawa-tengah',
                'author' => 'Admin',
                'published_at' => '2025-12-29 00:00:00',
                'excerpt' => 'SMK Negeri 2 Karanganyar meraih 14 prestasi tingkat nasional dan masuk 20 SMK terbaik Jawa Tengah, pada peringkat ke-15 berdasarkan akumulasi capaian prestasi siswa.',
                'content' => 'SMK Negeri 2 Karanganyar meraih 14 prestasi tingkat nasional dan masuk 20 SMK terbaik di Provinsi Jawa Tengah, pada peringkat ke-15 berdasarkan akumulasi capaian prestasi siswa. Prestasi tersebut diraih melalui berbagai kompetisi akademik dan nonakademik yang diselenggarakan Pusat Prestasi Nasional. Capaian ini didukung kerja keras siswa, bimbingan guru, dan dukungan manajemen sekolah.',
            ],
            [
                'category' => 'berita',
                'title' => 'Prestasi Membanggakan, Siswa SMK Negeri 2 Karanganyar Juara 1 Pencak Silat UNS Open XI 2025',
                'slug' => 'prestasi-membanggakan-siswa-smk-negeri-2-karanganyar-juara-1-pencak-silat-uns-open-xi-2025',
                'author' => 'Jurnalistik',
                'published_at' => '2025-12-28 00:00:00',
                'excerpt' => 'Sultan Eiger Okta Azizi dari kelas X MC meraih Juara 1 cabang Pencak Silat pada ajang UNS Open XI Tahun 2025.',
                'content' => 'Sultan Eiger Okta Azizi dari kelas X MC meraih Juara 1 cabang Pencak Silat pada ajang UNS Open XI Tahun 2025. Capaian ini merupakan hasil kerja keras, kedisiplinan, dan komitmen dalam mengembangkan bakat. Prestasi tersebut diharapkan dapat memotivasi siswa untuk terus mengembangkan potensi dan mengharumkan nama sekolah.',
            ],
            [
                'category' => 'pengumuman',
                'title' => 'SMK N 2 Karanganyar Terima Hibah Motor Listrik dari UNS–PLN untuk Perkuat Literasi Energi Bersih',
                'slug' => 'smk-n-2-karanganyar-terima-hibah-motor-listrik-dari-uns-pln-untuk-perkuat-literasi-energi-bersih',
                'author' => 'Admin Sekolah',
                'published_at' => '2025-12-01 00:00:00',
                'excerpt' => 'SMK Negeri 2 Karanganyar menerima satu unit motor listrik hasil konversi dari Fakultas Teknik UNS dan PT PLN untuk mendukung pembelajaran teknologi kendaraan listrik.',
                'content' => 'Kepala SMK Negeri 2 Karanganyar menghadiri EV Experience Day yang diselenggarakan Fakultas Teknik Universitas Sebelas Maret bersama PT PLN. Dalam kegiatan tersebut, sekolah menerima hibah satu unit motor listrik hasil konversi. Kendaraan ini diharapkan mendukung pembelajaran teknologi kendaraan listrik dan energi terbarukan.',
            ],
            [
                'category' => 'berita',
                'title' => 'Rekapitulasi Realisasi Penggunaan Dana BOSREG Tahap 1 SMKN 2 Karanganyar',
                'slug' => 'rekapitulasi-realisasi-penggunaan-dana-bosreg-tahap-1-smkn-2-karanganyar',
                'author' => 'Admin Sekolah',
                'published_at' => '2025-07-08 00:00:00',
                'excerpt' => 'Informasi rekapitulasi realisasi penggunaan Dana BOSREG Tahap 1 SMKN 2 Karanganyar.',
                'content' => 'SMK Negeri 2 Karanganyar menyampaikan informasi rekapitulasi realisasi penggunaan Dana BOSREG Tahap 1.',
            ],
            [
                'category' => 'kerja-sama-industri',
                'title' => 'Hasil Seleksi SPMB SMKN 2 Karanganyar Tahun 2025',
                'slug' => 'hasil-seleksi-spmb-smkn-2-karanganyar-tahun-2025',
                'author' => 'Agung Wiratmo',
                'published_at' => '2025-06-23 00:00:00',
                'excerpt' => 'Pengumuman hasil seleksi SPMB SMKN 2 Karanganyar Tahun 2025 untuk program Teknik Pemesinan, Teknik Ototronik, Teknologi Tekstil, dan Pengembangan Perangkat Lunak dan Gim.',
                'content' => 'SMKN 2 Karanganyar mengumumkan hasil seleksi SPMB Tahun 2025 untuk program keahlian yang tersedia. Calon peserta didik dapat memeriksa hasil melalui kanal resmi sekolah. Seeder ini tidak memuat nomor pendaftaran atau data identitas peserta.',
            ],
            [
                'category' => 'kerja-sama-industri',
                'title' => 'Flayer Sistem Penerimaan Peserta Didik Baru SMKN 2 Karanganyar Tahun Ajaran 2025/2026',
                'slug' => 'flayer-sistem-penerimaan-peserta-didik-baru-smkn-2-karanganyar-tahun-ajaran-2025-2026',
                'author' => 'Agung Wiratmo',
                'published_at' => '2025-05-19 00:00:00',
                'excerpt' => 'Informasi visual Sistem Penerimaan Peserta Didik Baru SMKN 2 Karanganyar Tahun Ajaran 2025/2026.',
                'content' => 'Informasi Sistem Penerimaan Peserta Didik Baru SMKN 2 Karanganyar Tahun Ajaran 2025/2026. Gambar publikasi dapat ditambahkan melalui pengelolaan media setelah data seeder dimuat.',
            ],
            [
                'category' => 'berita',
                'title' => 'Pelepasan Siswa Siswi Kelas XII dan XIII Tahun Ajaran 2024/2025',
                'slug' => 'pelepasan-siswa-siswi-kelas-xii-dan-xiii-tahun-ajaran-2024-2025',
                'author' => 'Agung Wiratmo',
                'published_at' => '2025-05-05 00:00:00',
                'excerpt' => 'Pelepasan siswa-siswi kelas XII dan XIII dilaksanakan di aula sekolah dan dihadiri orang tua serta wali siswa.',
                'content' => 'Pelepasan siswa-siswi kelas XII dan XIII SMK Negeri 2 Karanganyar dilaksanakan di aula sekolah dan dihadiri orang tua serta wali. Acara ini menandai berakhirnya perjalanan pendidikan di bangku SMK sekaligus awal langkah baru menuju dunia kerja atau pendidikan tinggi.',
            ],
            [
                'category' => 'berita',
                'title' => 'Upacara Hari Pendidikan Nasional 2025',
                'slug' => 'upacara-hari-pendidikan-nasional-2025',
                'author' => 'Agung Wiratmo',
                'published_at' => '2025-05-05 00:00:00',
                'excerpt' => 'Warga SMK Negeri 2 Karanganyar mengikuti upacara Hari Pendidikan Nasional 2025 di Lapangan Timur.',
                'content' => 'SMK Negeri 2 Karanganyar menyelenggarakan upacara bendera untuk memperingati Hari Pendidikan Nasional 2025. Upacara diikuti siswa kelas X, XI, XII, dan XIII serta bapak dan ibu guru. Kegiatan berlangsung di Lapangan Timur mulai pukul 07.00 WIB.',
            ],
            [
                'category' => 'berita',
                'title' => 'Rekap LKS SMK Jateng Ke-33 Teknologi Informasi Perangkat Lunak untuk Bisnis Berjalan Lancar',
                'slug' => 'rekap-lks-smk-jateng-ke-33-teknologi-informasi-perangkat-lunak-untuk-bisnis-berjalan-lancar',
                'author' => 'Agung Wiratmo',
                'published_at' => '2025-04-30 00:00:00',
                'excerpt' => 'Lomba Kompetensi Siswa SMK Jateng Ke-33 bidang Teknologi Informasi Perangkat Lunak untuk Bisnis digelar di SMKN 2 Karanganyar pada 28–30 April 2025.',
                'content' => 'Lomba Kompetensi Siswa SMK Jateng Ke-33 Tahun 2025 menjadi ajang unjuk kemampuan siswa bidang Teknologi Informasi Perangkat Lunak untuk Bisnis. Kegiatan digelar di SMKN 2 Karanganyar pada 28–30 April 2025 dan diikuti peserta dari kabupaten/kota di Jawa Tengah. Peserta mengerjakan modul desktop dengan C# dan SQL Server, aplikasi Android yang mengakses API, serta Web API menggunakan ASP.NET.',
            ],
        ];

        foreach ($articles as $articleData) {
            $category = ArticleCategory::query()->where('slug', $articleData['category'])->first();
            if ($category === null) {
                continue;
            }

            $article = Article::query()->firstOrNew(['slug' => $articleData['slug']]);
            $article->fill([
                'category_id' => $category->id,
                'author_id' => $this->author($articleData['author'])->id,
                'title' => $articleData['title'],
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content'],
                'status' => ContentStatus::Published,
                'published_at' => $articleData['published_at'],
            ]);
            $article->save();
        }
    }

    private function author(string $name): User
    {
        $username = 'article.author.'.Str::slug($name);

        return User::query()->firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $username.'@example.invalid',
                'password' => Hash::make(Str::random(48)),
                'is_active' => false,
            ],
        );
    }
}
