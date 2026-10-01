<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('username', 'guru.agus')->first() ?? User::query()->first();

        if ($author === null) {
            $this->command?->warn('Artikel tidak dapat diisi: belum ada pengguna sebagai penulis.');

            return;
        }

        $articles = [
            [
                'category' => 'kegiatan-sekolah',
                'title' => 'Pekan Kreativitas Siswa 2026 Pamerkan Karya Unggulan',
                'slug' => 'pekan-kreativitas-siswa-2026',
                'excerpt' => 'Selama tiga hari siswa memamerkan karya terbaik, mulai dari aplikasi digital hingga produk kreatif tiap program keahlian.',
                'published_at' => '2026-09-18 08:00:00',
                'views' => 412,
            ],
            [
                'category' => 'ppdb',
                'title' => 'PPDB Tahun Pelajaran 2026/2027 Resmi Dibuka',
                'slug' => 'ppdb-tahun-pelajaran-2026-2027-dibuka',
                'excerpt' => 'Pendaftaran peserta didik baru dibuka untuk lima program keahlian dengan kuota terbatas dan jalur seleksi prestasi.',
                'published_at' => '2026-09-10 07:30:00',
                'views' => 1187,
            ],
            [
                'category' => 'karier-dan-alumni',
                'title' => 'Penyaluran Kerja: 42 Lulusan Diterima Mitra Industri',
                'slug' => 'penyaluran-kerja-42-lulusan-diterima',
                'excerpt' => 'Bursa Kerja Sekolah bekerja sama dengan sebelas mitra industri membuka lowongan bagi lulusan program keahlian.',
                'published_at' => '2026-09-03 09:15:00',
                'views' => 736,
            ],
            [
                'category' => 'pengumuman',
                'title' => 'Jadwal Ujian Tengah Semester dan Pengesahan Nilai',
                'slug' => 'jadwal-ujian-tengah-semester',
                'excerpt' => 'Informasi jadwal ujian tengah semester, mekanisme pengawasan, serta pengesahan nilai melalui buku nilai digital.',
                'published_at' => '2026-08-27 10:00:00',
                'views' => 902,
            ],
            [
                'category' => 'prestasi',
                'title' => 'Tim Ototronik Raih Juara Dua Lomba Kompetensi',
                'slug' => 'tim-ototronik-juara-dua-lomba-kompetensi',
                'excerpt' => 'Karya siswa berupa overhaul mesin dipamerkan di tingkat nasional dan mendapat penghargaan.',
                'published_at' => '2026-08-19 13:00:00',
                'views' => 655,
            ],
            [
                'category' => 'kegiatan-sekolah',
                'title' => 'Gelar Kerja Sama PKL untuk Semester Genap',
                'slug' => 'gelar-kerja-sama-pkl-semester-genap',
                'excerpt' => 'Siswa menerima penempatan praktik kerja lapangan di perusahaan mitra yang tersebar di beberapa kota.',
                'published_at' => '2026-08-11 08:45:00',
                'views' => 388,
            ],
            [
                'category' => 'karier-dan-alumni',
                'title' => 'Tracer Study: 96 Persen Lulusan Terserap',
                'slug' => 'tracer-study-96-persen-alumni-terserap',
                'excerpt' => 'Hasil penelusuran alumni menunjukkan tingkat penyerapan tinggi pada enam bulan pertama setelah lulus.',
                'published_at' => '2026-07-30 11:00:00',
                'views' => 544,
            ],
            [
                'category' => 'pengumuman',
                'title' => 'Penyesuaian Jam Masuk Selama Pengenalan Lingkungan Sekolah',
                'slug' => 'penyesuaian-jam-masuk-pengenalan-lingkungan',
                'excerpt' => 'Selama dua pekan pertama semester ganjil, kegiatan pembelajaran dimulai pukul 07.15 untuk kegiatan orientasi.',
                'published_at' => '2026-07-22 07:00:00',
                'views' => 318,
            ],
        ];

        foreach ($articles as $article) {
            $category = ArticleCategory::where('slug', $article['category'])->first();

            if ($category === null) {
                continue;
            }

            Article::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $this->content($article['excerpt']),
                    'thumbnail' => null,
                    'status' => ContentStatus::Published,
                    'published_at' => $article['published_at'],
                    'views' => $article['views'],
                ],
            );
        }
    }

    /**
     * Placeholder article body. The CMS stores the full text, so a short
     * lead-in keeps the detail page readable until an editor replaces it.
     */
    private function content(string $excerpt): string
    {
        return implode("\n\n", [
            $excerpt,
            'Kegiatan ini merupakan program pengembangan kompetensi siswa yang berjalan setiap tahun.',
            'Informasi lengkap dapat diperoleh melalui wali kelas atau tata usaha sekolah pada jam kerja.',
        ]);
    }
}
