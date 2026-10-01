<?php

namespace App\Services;

use App\Enums\CareerOpportunityStatus;
use App\Enums\ContentStatus;
use App\Models\Achievement;
use App\Models\AdmissionPeriod;
use App\Models\AlumniStory;
use App\Models\Article;
use App\Models\CareerOpportunity;
use App\Models\Department;
use App\Models\SiteStatistic;
use App\Models\StudentProduct;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Retrieval-backed answer engine for the public school chatbot.
 *
 * Every answer is composed from the live CMS tables (departments, admission
 * periods, articles, achievements, products, career opportunities and the
 * school profile), so the assistant never repeats a stale hardcoded fact:
 * whatever an administrator edits shows up on the very next question.
 *
 * Matching is intentionally simple and dependency-free. The visitor question is
 * normalised, then scored against a small keyword table; the highest scoring
 * intent wins and renders its own answer from the database. When nothing scores
 * the visitor still gets a useful fallback listing what the bot can talk about.
 *
 * All database access goes through `guard()`, which turns a failing query into
 * `null`: the chatbot keeps answering from the remaining sources instead of
 * returning a 500.
 */
class PublicChatbotService
{
    /**
     * Keyword to intent table.
     *
     * Keywords are matched as *word roots*: `jurusan` also matches "jurusannya",
     * `daftar` matches "mendaftar", `kerja` matches "pekerjaan". That is what
     * makes the everyday Indonesian phrasing visitors type resolve correctly.
     *
     * @var list<array{intent: string, keywords: list<string>}>
     */
    private const INTENTS = [
        ['intent' => 'greeting', 'keywords' => ['assalamualaikum', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'halo', 'hai', 'hello', 'hi', 'pagi', 'siang', 'sore', 'malam']],
        ['intent' => 'thanks', 'keywords' => ['terima kasih', 'mksh', 'makasih', 'thanks', 'thank you']],
        ['intent' => 'contact', 'keywords' => ['kontak', 'telepon', 'nomor hp', 'no hp', 'email', 'alamat', 'lokasi sekolah', 'direksi', 'peta']],
        ['intent' => 'vision', 'keywords' => ['visi', 'misi', 'sejarah', 'profil sekolah', 'deskripsi sekolah', 'tentang sekolah']],
        ['intent' => 'achievement', 'keywords' => ['prestasi', 'juara', 'lomba', 'olimpiade', 'medali', 'podium', 'penghargaan']],
        ['intent' => 'alumni', 'keywords' => ['alumni', 'lulusan', 'kuliah', 'perguruan tinggi', 'beasiswa']],
        ['intent' => 'statistics', 'keywords' => ['jumlah siswa', 'siswa aktif', 'jumlah guru', 'guru aktif', 'jumlah jurusan', 'statistik', 'berapa siswa', 'berapa guru']],
        ['intent' => 'news', 'keywords' => ['berita', 'artikel', 'pengumuman', 'info terbaru', 'kegiatan', 'acara', 'agenda']],
        ['intent' => 'career', 'keywords' => ['pkl', 'magang', 'karier', 'lowongan', 'kerja', 'bkk', 'konseling', 'mitra', 'perusahaan', 'job', 'karir']],
        ['intent' => 'product', 'keywords' => ['produk', 'jasa', 'harga', 'katalog', 'makanan', 'ketos', 'baju', 'sepatu']],
        ['intent' => 'admission', 'keywords' => ['ppdb', 'pendaftaran', 'daftar', 'admisi', 'jalur', 'syarat', 'biaya', 'uang pangkal', 'gelombang', 'pendaftar', 'kuota']],
        ['intent' => 'department', 'keywords' => ['jurusan', 'kompetensi', 'prodi', 'program keahlian', 'mata pelajaran', 'sertifikasi', 'praktikum', 'kelas', 'sekolah']],
    ];

    /**
     * Words that mean "give me the whole thing". A vague question gets a short
     * summary while a specific one gets full detail, so "ppdb" and
     * "syarat PPDB lengkap" produce genuinely different answers instead of the
     * same wall of text twice.
     *
     * @var list<string>
     */
    private const DETAIL_MARKERS = [
        'detail', 'lengkap', 'semua', 'info', 'jadwal', 'syarat', 'biaya', 'nama',
        'visa', 'usia', 'umur', 'waktu', 'cara', 'bagaimana', 'gimana', 'prosedur',
        'ketentuan', 'nilai', 'beasiswa', 'jalur', 'dokumen', 'berkas', 'kuota',
    ];

    /**
     * @var list<string>
     */
    private const FALLBACK_SUGGESTIONS = [
        'Jurusan apa saja?',
        'Kapan PPDB dibuka?',
        'Ada lowongan PKL?',
        'Produk siswa apa saja?',
        'Kontak sekolah?',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $memo = [];

    public function __construct(private readonly PublicSiteService $publicSite) {}

    /**
     * Opening message shown whenever the chat panel is empty.
     *
     * @return array{reply: string, suggestions: list<string>}
     */
    public function opening(): array
    {
        $name = $this->schoolName();

        return [
            'reply' => "Halo! Saya asisten virtual {$name}.\n"
                .'Saya terhubung langsung ke data sekolah, jadi saya bisa bantu soal jurusan, PPDB, PKL, produk karya siswa, prestasi, sampai kontak sekolah.',
            'suggestions' => self::FALLBACK_SUGGESTIONS,
        ];
    }

    /**
     * Answer a visitor question using live database content.
     *
     * @return array{
     *     intent: string,
     *     reply: string,
     *     suggestions: list<string>,
     *     links: list<array{label: string, url: string}>
     * }
     */
    public function answer(string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return $this->reply('empty', 'Silakan tulis pertanyaanmu dulu, ya.', self::FALLBACK_SUGGESTIONS);
        }

        $detail = $this->wantsDetail($question);

        return match ($this->detectIntent($question)) {
            'greeting' => $this->greetingAnswer(),
            'thanks' => $this->thanksAnswer(),
            'contact' => $this->contactAnswer(),
            'admission' => $this->admissionAnswer($detail),
            'career' => $this->careerAnswer($detail),
            'product' => $this->productAnswer($question, $detail),
            'news' => $this->newsAnswer($detail),
            'achievement' => $this->achievementAnswer($detail),
            'alumni' => $this->alumniAnswer($detail),
            'statistics' => $this->statisticsAnswer(),
            'vision' => $this->visionAnswer(),
            'department' => $this->departmentAnswer($question, $detail),
            default => $this->fallbackAnswer(),
        };
    }

    /* =========================================================
       INTENT MATCHING
       ========================================================= */

    /**
     * Pick the intent that best explains the question.
     *
     * Scoring is dominated by keyword specificity, because a longer root is a
     * stronger signal than a short generic one: in "daftar produk" `produk`
     * (7 chars) must outrank `daftar` (5 chars). Position breaks the remaining
     * ties, and the head noun of an Indonesian question usually comes last, so
     * "daftar produk" resolves to products rather than to admission.
     *
     * Department *names* are deliberately not part of that table. They only act
     * as a fallback when no topic keyword matched at all, so "pkl untuk jurusan
     * RPL" still routes to PKL while "Tentang RPL" reaches the department page.
     */
    private function detectIntent(string $question): string
    {
        $haystack = $this->normalize($question);
        $best = '';
        $bestScore = 0;
        $bestPosition = -1;

        foreach (self::INTENTS as $group) {
            $score = 0;
            $position = -1;

            foreach ($group['keywords'] as $keyword) {
                $offset = $this->rootPosition($haystack, $keyword);

                if ($offset === null) {
                    continue;
                }

                $score += mb_strlen($keyword);
                $position = max($position, $offset);
            }

            if ($score === 0) {
                continue;
            }

            if ($score > $bestScore || ($score === $bestScore && $position > $bestPosition)) {
                $bestScore = $score;
                $bestPosition = $position;
                $best = $group['intent'];
            }
        }

        if ($best !== '') {
            return $best;
        }

        return $this->matchDepartment($question) !== null ? 'department' : '';
    }

    /**
     * Character offset where `keyword` appears as the root of a word, or null.
     *
     * Root matching is anchored at a word start, so `hi` never fires inside
     * "histori", while the trailing `\w*` is what lets `jurusan` match
     * "jurusannya" and `daftar` match "mendaftar".
     */
    private function rootPosition(string $haystack, string $keyword): ?int
    {
        $needle = $this->normalize($keyword);

        if ($needle === '') {
            return null;
        }

        $pattern = '/(?:^|\s)'.preg_quote($needle, '/').'\w*/u';

        return preg_match($pattern, $haystack, $matches, PREG_OFFSET_CAPTURE) === 1
            ? $matches[0][1]
            : null;
    }

    /**
     * Whether the visitor asked for the full picture rather than a summary.
     */
    private function wantsDetail(string $question): bool
    {
        $haystack = $this->normalize($question);

        foreach (self::DETAIL_MARKERS as $marker) {
            if ($this->rootPosition($haystack, $marker) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lowercase the text and collapse punctuation into single spaces so keyword
     * matching is unaffected by commas, slashes or repeated whitespace.
     */
    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /* =========================================================
       ANSWERS
       ========================================================= */

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function greetingAnswer(): array
    {
        return $this->reply(
            'greeting',
            "Halo! Selamat datang di website {$this->schoolName()}.\n"
            .'Ada yang bisa saya bantu hari ini?',
            ['Jurusan apa saja yang tersedia?', 'Kapan PPDB dibuka?', 'Ada lowongan PKL sekarang?'],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function thanksAnswer(): array
    {
        return $this->reply(
            'thanks',
            'Sama-sama! Senang bisa membantu. Kalau masih ada pertanyaan lain, silakan ditanyakan kapan saja.',
            ['Lihat produk karya siswa', 'Lihat berita terbaru'],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function contactAnswer(): array
    {
        $profile = $this->publicSite->profile();

        if ($profile === null) {
            return $this->reply('contact', 'Data kontak sekolah belum diisi oleh admin.', ['Jurusan apa saja?']);
        }

        $lines = ["**{$profile->school_name}**"];

        foreach ([
            'Alamat' => $profile->address,
            'Telepon' => $profile->phone,
            'Email' => $profile->email,
            'Website' => $profile->website,
            'Kepala sekolah' => $profile->principal_name,
        ] as $label => $value) {
            if (filled($value)) {
                $lines[] = "{$label}: {$value}";
            }
        }

        return $this->reply(
            'contact',
            implode("\n", $lines),
            ['Kapan PPDB dibuka?', 'Jurusan apa saja?'],
            [['label' => 'Halaman Profil', 'url' => route('public.profile')]],
        );
    }

    /**
     * A vague "ppdb" gets the headline facts; "syarat PPDB" or
     * "PPDB lengkap" gets the full requirements, fees and stages.
     *
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function admissionAnswer(bool $detail): array
    {
        $periods = $this->guard(fn (): Collection => AdmissionPeriod::query()
            ->with([
                'academicYear',
                'scheduleItems',
                'paths' => fn ($query) => $query->where('is_active', true),
                'requirements',
                'feeItems',
            ])
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['OPEN'])
            ->orderByDesc('registration_start')
            ->get()) ?? new Collection;

        if ($periods->isEmpty()) {
            return $this->reply(
                'admission',
                'Belum ada periode PPDB yang dipublikasikan. Pantau halaman PPDB untuk informasi terbaru.',
                ['Kontak sekolah', 'Lihat berita terbaru'],
                [['label' => 'Halaman PPDB', 'url' => route('public.ppdb.index')]],
            );
        }

        $open = $periods->firstWhere('status', 'OPEN') ?? $periods->first();
        $isOpen = $open->status === 'OPEN';
        $year = $open->academicYear?->name;

        $headline = "**{$open->title}**"
            .($year !== null && $year !== '' ? " untuk tahun pelajaran {$year}" : '')
            .($isOpen ? ' sedang dibuka.' : ' sudah ditutup.');

        $lines = [
            $headline,
            'Periode pendaftaran: '.$this->formatDate($open->registration_start)
                .' sampai '.$this->formatDate($open->registration_end).'.',
        ];

        if ($open->paths->isNotEmpty()) {
            $lines[] = 'Jalur: '.$this->bulletList($open->paths->pluck('name')->all());
        }

        if ($detail) {
            if ($open->requirements->isNotEmpty()) {
                $lines[] = 'Syarat berkas: '.$this->bulletList($open->requirements->pluck('title')->all());
            }

            $fees = $open->feeItems
                ->reject(fn ($item): bool => (bool) $item->is_free)
                ->map(fn ($item): string => $item->name.' Rp '.number_format((float) $item->amount, 0, ',', '.'))
                ->all();

            if ($fees !== []) {
                $lines[] = 'Biaya: '.$this->bulletList($fees)
                    .'. Total '.$this->formatNumber(
                        (float) $open->feeItems->reject(fn ($item): bool => (bool) $item->is_free)->sum('amount'),
                    ).'.';
            }

            if ($open->scheduleItems->isNotEmpty()) {
                $lines[] = 'Tahapan: '.$this->bulletList($open->scheduleItems->pluck('title')->all(), 4);
            }
        }

        return $this->reply(
            'admission',
            implode("\n", $lines),
            $detail
                ? ['Ada jalur lain yang dibuka?', 'Lowongan PKL sekarang?']
                : ['Syarat PPDB apa saja?', 'Berapa biaya daftar?', 'Jadwal PPDB lengkap'],
            [['label' => 'Detail PPDB', 'url' => route('public.ppdb.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function careerAnswer(bool $detail): array
    {
        $opportunities = $this->guard(fn (): Collection => CareerOpportunity::query()
            ->with('company')
            ->where('status', CareerOpportunityStatus::Open)
            ->orderByDesc('open_date')
            ->limit($detail ? 5 : 3)
            ->get()) ?? new Collection;

        if ($opportunities->isEmpty()) {
            return $this->reply(
                'career',
                'Belum ada lowongan atau magang yang dibuka saat ini. Pantau Career Center untuk informasi terbaru dan layanan BKK.',
                ['Layanan BKK apa saja?', 'Kontak sekolah'],
                [['label' => 'Career Center', 'url' => route('public.career.index')]],
            );
        }

        $lines = ['Lowongan dan magang yang sedang dibuka:'];

        foreach ($opportunities as $opportunity) {
            $company = $opportunity->company?->name ?? 'Perusahaan mitra';
            $type = $opportunity->type?->label() ?? 'Lowongan';
            $location = filled($opportunity->location) ? " di {$opportunity->location}" : '';
            $closing = $detail && filled($opportunity->close_date)
                ? ' (tutup '.$this->formatDate($opportunity->close_date).')'
                : '';

            $lines[] = "• **{$opportunity->title}** - {$type} {$company}{$location}{$closing}";
        }

        return $this->reply(
            'career',
            implode("\n", $lines),
            $detail ? ['Jurusan apa saja?', 'Kontak sekolah'] : ['Layanan BKK apa saja?', 'Jurusan apa saja?'],
            [['label' => 'Semua Lowongan', 'url' => route('public.career.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function productAnswer(string $question, bool $detail): array
    {
        $matched = $this->matchDepartment($question);

        $products = $this->guard(fn (): Collection => StudentProduct::query()
            ->with(['category', 'department'])
            ->where('status', 'AVAILABLE')
            ->when($matched !== null, fn ($query) => $query->where('department_id', $matched->id))
            ->orderByDesc('id')
            ->limit($detail ? 8 : 5)
            ->get()) ?? new Collection;

        if ($products->isEmpty()) {
            return $this->reply(
                'product',
                $matched !== null
                    ? "Belum ada produk karya siswa dari jurusan {$matched->name} yang tersedia."
                    : 'Belum ada produk karya siswa yang tersedia saat ini.',
                ['Jurusan apa saja?', 'Lihat berita terbaru'],
                [['label' => 'Katalog Produk', 'url' => route('public.products.index')]],
            );
        }

        $scope = $matched !== null ? " dari jurusan {$matched->name}" : '';
        $lines = ["Produk karya siswa yang tersedia{$scope}:"];

        foreach ($products as $product) {
            $price = $product->price !== null
                ? 'Rp '.number_format((float) $product->price, 0, ',', '.')
                : 'hubungi sekolah untuk harga';
            $category = $product->category?->name;

            $lines[] = '• **'.$product->name.'**'
                .($category !== null ? " ({$category})" : '')
                ." - {$price}";
        }

        return $this->reply(
            'product',
            implode("\n", $lines),
            $this->departmentSuggestions(),
            [['label' => 'Lihat Katalog', 'url' => route('public.products.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function newsAnswer(bool $detail): array
    {
        $articles = $this->guard(fn (): Collection => Article::query()
            ->with('category')
            ->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit($detail ? 5 : 3)
            ->get()) ?? new Collection;

        if ($articles->isEmpty()) {
            return $this->reply(
                'news',
                'Belum ada artikel yang dipublikasikan. Silakan kembali lagi nanti.',
                ['Lihat prestasi', 'Kontak sekolah'],
                [['label' => 'Halaman Berita', 'url' => route('public.articles.index')]],
            );
        }

        $lines = ['Berita terbaru:'];

        foreach ($articles as $article) {
            $category = $detail && $article->category !== null ? " ({$article->category->name})" : '';

            $lines[] = "• **{$article->title}**{$category} - ".$this->formatDate($article->published_at);
        }

        return $this->reply(
            'news',
            implode("\n", $lines),
            ['Apa prestasi terbaru?', 'Ada acara apa saja?'],
            [['label' => 'Semua Berita', 'url' => route('public.articles.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function achievementAnswer(bool $detail): array
    {
        $achievements = $this->guard(fn (): Collection => Achievement::query()
            ->with('category')
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->limit($detail ? 5 : 3)
            ->get()) ?? new Collection;

        if ($achievements->isEmpty()) {
            return $this->reply(
                'achievement',
                'Belum ada data prestasi yang dipublikasikan.',
                ['Lihat berita terbaru', 'Kontak sekolah'],
                [['label' => 'Halaman Prestasi', 'url' => route('public.achievements.index')]],
            );
        }

        $lines = ['Prestasi terbaru:'];

        foreach ($achievements as $achievement) {
            $meta = collect([$achievement->level, $achievement->scope, $achievement->rank])
                ->filter()
                ->implode(' / ');

            $lines[] = '• **'.$achievement->title.'**'
                .($meta !== '' ? " ({$meta})" : '')
                .' - '.$this->formatDate($achievement->achievement_date);
        }

        return $this->reply(
            'achievement',
            implode("\n", $lines),
            ['Siapa kepala sekolahnya?', 'Cerita alumni'],
            [['label' => 'Semua Prestasi', 'url' => route('public.achievements.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function alumniAnswer(bool $detail): array
    {
        $stories = $this->guard(fn (): Collection => AlumniStory::query()
            ->with(['alumniProfile.student'])
            ->where('is_featured', true)
            ->orderByDesc('id')
            ->limit($detail ? 4 : 2)
            ->get()) ?? new Collection;

        if ($stories->isEmpty()) {
            return $this->reply(
                'alumni',
                'Cerita alumni unggulan belum dipublikasikan. Kisah alumni lain bisa kamu baca di halaman Alumni.',
                ['Jurusan apa saja?', 'Lihat prestasi'],
                [['label' => 'Halaman Alumni', 'url' => route('public.alumni.index')]],
            );
        }

        $lines = ['Cerita alumni:'];

        foreach ($stories as $story) {
            $name = $story->alumniProfile?->student?->name ?? 'Alumni sekolah';
            $lines[] = "• **{$story->title}** - {$name}";

            if (filled($story->career_story)) {
                $lines[] = '  '.$this->clip($story->career_story, 180);
            }
        }

        return $this->reply(
            'alumni',
            implode("\n", $lines),
            ['Ada lowongan kerja untuk alumni?', 'Lihat prestasi'],
            [['label' => 'Semua Alumni', 'url' => route('public.alumni.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function statisticsAnswer(): array
    {
        $statistics = $this->guard(fn (): Collection => SiteStatistic::query()
            ->where('is_active', true)
            ->where('section', 'HERO')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()) ?? new Collection;

        if ($statistics->isEmpty()) {
            return $this->reply(
                'statistics',
                'Statistik resmi sekolah belum diisi oleh admin. Silakan hubungi sekolah untuk data terbaru.',
                ['Kontak sekolah', 'Visi dan misi sekolah'],
                [['label' => 'Profil Sekolah', 'url' => route('public.profile')]],
            );
        }

        $lines = ['Angka resmi sekolah:'];

        foreach ($statistics as $statistic) {
            $lines[] = "• {$statistic->label}: **{$statistic->value}**";
        }

        return $this->reply(
            'statistics',
            implode("\n", $lines),
            ['Jurusan apa saja?', 'Kontak sekolah'],
            [['label' => 'Profil Sekolah', 'url' => route('public.profile')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function visionAnswer(): array
    {
        $profile = $this->publicSite->profile();

        if ($profile === null) {
            return $this->reply(
                'vision',
                'Profil sekolah belum diisi oleh admin. Silakan cek lagi nanti.',
                ['Kontak sekolah'],
                [['label' => 'Halaman Profil', 'url' => route('public.profile')]],
            );
        }

        $lines = [];

        if (filled($profile->description)) {
            $lines[] = $this->clip($profile->description, 320);
        }

        foreach ([
            'Visi' => $profile->vision,
            'Misi' => $profile->mission,
            'Sejarah' => $profile->history,
        ] as $label => $value) {
            if (filled($value)) {
                $lines[] = "**{$label}** - ".$this->clip($value, 260);
            }
        }

        if ($lines === []) {
            $lines[] = 'Profil sekolah belum diisi lengkap. Silakan cek halaman profil untuk informasi lengkap.';
        }

        return $this->reply(
            'vision',
            implode("\n", $lines),
            ['Kontak sekolah', 'Jurusan apa saja?'],
            [['label' => 'Halaman Profil', 'url' => route('public.profile')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function departmentAnswer(string $question, bool $detail): array
    {
        $departments = $this->departments();

        if ($departments->isEmpty()) {
            return $this->reply(
                'department',
                'Data jurusan belum diisi oleh admin sekolah.',
                [],
                [['label' => 'Halaman Jurusan', 'url' => route('public.departments.index')]],
            );
        }

        $matched = $this->matchDepartment($question);

        if ($matched === null) {
            $lines = ['Ada '.$departments->count().' program keahlian yang aktif:'];

            foreach ($departments as $department) {
                $short = filled($department->short_name) ? " ({$department->short_name})" : '';
                $lines[] = "• {$department->name}{$short}";
            }

            $lines[] = 'Ketik nama jurusannya kalau mau tahu detail, kompetensi, dan prospek karier.';

            return $this->reply(
                'department',
                implode("\n", $lines),
                $this->departmentSuggestions(),
                [['label' => 'Semua Jurusan', 'url' => route('public.departments.index')]],
            );
        }

        return $this->reply(
            'department',
            $this->departmentSummary($matched, $detail),
            $detail
                ? ['Produk dari jurusan ini', 'Lowongan PKL apa saja?']
                : ['Kompetensi di jurusan ini', 'Prospek karier jurusan ini', 'Produk dari jurusan ini'],
            [['label' => 'Detail Jurusan', 'url' => route('public.departments.show', $matched)]],
        );
    }

    private function departmentSummary(Department $department, bool $detail = true): string
    {
        $lines = ["**{$department->name}**"];

        if (filled($department->description)) {
            $lines[] = $this->clip($department->description, 320);
        }

        $competencies = $this->guard(fn (): array => $department->competencies()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('title')
            ->all()) ?? [];

        if ($competencies !== []) {
            $lines[] = 'Kompetensi yang dilatih: '.$this->bulletList($competencies, $detail ? 8 : 4);
        }

        if (filled($department->career_prospects)) {
            $lines[] = 'Prospek karier: '.$this->clip($department->career_prospects, 260);
        }

        if ($detail) {
            $products = $this->guard(fn (): array => $department->studentProducts()
                ->where('status', 'AVAILABLE')
                ->orderByDesc('id')
                ->limit(4)
                ->pluck('name')
                ->all()) ?? [];

            if ($products !== []) {
                $lines[] = 'Produk karya siswa: '.$this->bulletList($products, 4);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function fallbackAnswer(): array
    {
        $known = ['berita, prestasi, dan cerita alumni terbaru', 'alamat, telepon, dan email sekolah'];

        if ($this->departments()->isNotEmpty()) {
            array_unshift($known, 'daftar jurusan dan kompetensi tiap jurusan');
        }

        if ($this->countWhere(AdmissionPeriod::query()->where('status', 'OPEN')) > 0) {
            array_unshift($known, 'jadwal, jalur, syarat, dan biaya PPDB');
        }

        if ($this->countWhere(CareerOpportunity::query()->where('status', CareerOpportunityStatus::Open)) > 0) {
            array_unshift($known, 'lowongan kerja dan magang dari mitra industri');
        }

        if ($this->countWhere(StudentProduct::query()->where('status', 'AVAILABLE')) > 0) {
            array_unshift($known, 'produk dan jasa karya siswa lengkap dengan harganya');
        }

        return $this->reply(
            'fallback',
            "Maaf, aku belum menemukan jawaban yang pas untuk pertanyaan itu.\n"
            .'Tapi aku baca langsung data sekolah, jadi aku bisa bantu soal: '.implode(', ', $known).'.',
            self::FALLBACK_SUGGESTIONS,
        );
    }

    /* =========================================================
       HELPERS
       ========================================================= */

    /**
     * Build a consistent answer payload.
     *
     * @param  list<string>  $suggestions
     * @param  list<array{label: string, url: string}>  $links
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function reply(string $intent, string $reply, array $suggestions = [], array $links = []): array
    {
        return [
            'intent' => $intent,
            'reply' => $reply,
            'suggestions' => array_slice(array_values($suggestions), 0, 3),
            'links' => array_slice(array_values($links), 0, 2),
        ];
    }

    /**
     * Active departments, memoised for the lifetime of the request.
     *
     * @return Collection<int, Department>
     */
    private function departments(): Collection
    {
        return $this->memo['departments'] ??= $this->publicSite->activeDepartments();
    }

    /**
     * @return list<string>
     */
    private function departmentSuggestions(): array
    {
        return $this->departments()
            ->take(3)
            ->map(fn (Department $department): string => 'Tentang '.($department->short_name ?: $department->name))
            ->values()
            ->all();
    }

    /**
     * Find the department named in the question. The full name, the short name
     * and the code are all tried, so "rpl" and "Rekayasa Perangkat Lunak"
     * resolve to the same department.
     */
    private function matchDepartment(string $question): ?Department
    {
        $haystack = ' '.$this->normalize($question).' ';
        $best = null;
        $bestLength = 0;

        foreach ($this->departments() as $department) {
            // Longest alias wins, so "Rekayasa Perangkat Lunak" beats a bare
            // code collision rather than depending on iteration order.
            foreach ([$department->name, $department->short_name, $department->code] as $alias) {
                $needle = $this->normalize((string) $alias);

                if ($needle === '' || mb_strlen($needle) <= $bestLength) {
                    continue;
                }

                if (str_contains($haystack, ' '.$needle.' ')) {
                    $best = $department;
                    $bestLength = mb_strlen($needle);
                }
            }
        }

        return $best;
    }

    /**
     * Render a compact comma separated list, truncated when it gets too long.
     *
     * @param  list<string>  $items
     */
    private function bulletList(array $items, int $limit = 5): string
    {
        $items = array_values(array_filter(array_map('trim', $items)));

        if ($items === []) {
            return '-';
        }

        if (count($items) > $limit) {
            return implode(', ', array_slice($items, 0, $limit)).', dan lain-lain';
        }

        return implode(', ', $items);
    }

    private function clip(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'...';
    }

    /**
     * Indonesian date formatting that does not depend on the application locale,
     * which is English in this project.
     */
    private function formatDate(DateTimeInterface|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        try {
            $moment = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

            return $moment->day.' '.$months[$moment->month].' '.$moment->year;
        } catch (Throwable) {
            return '-';
        }
    }

    /**
     * Indonesian currency formatting, matching the dot separated thousands the
     * rest of the public site uses.
     */
    private function formatNumber(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    /**
     * Run a database query and turn any failure into `null`, so the chatbot
     * keeps answering from the remaining sources instead of erroring out.
     */
    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function countWhere(Builder $query): int
    {
        return (int) ($this->guard(fn (): int => $query->count()) ?? 0);
    }

    private function schoolName(): string
    {
        return $this->publicSite->schoolName();
    }
}
