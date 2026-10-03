<?php

namespace Tests\Feature;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Enums\ContentStatus;
use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AdmissionPeriod;
use App\Models\AlumniProfile;
use App\Models\AlumniStory;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\CareerService;
use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\SchoolProfile;
use App\Models\SiteStatistic;
use App\Models\StudentProduct;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\PublicMediaService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicSitePagesTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SchoolProfileSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(UserSeeder::class);

        $this->author = User::where('username', 'guru.agus')->firstOrFail();
    }

    public function test_every_public_page_responds_successfully(): void
    {
        $urls = ['/', '/profil', '/jurusan', '/berita', '/prestasi', '/alumni', '/ppdb', '/produk-siswa', '/karier'];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_public_pages_render_without_cdn_assets_and_with_balanced_markup(): void
    {
        $urls = ['/', '/profil', '/jurusan', '/berita', '/prestasi', '/alumni', '/ppdb', '/produk-siswa', '/karier'];

        foreach ($urls as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '#(cdn\.tailwindcss|fonts\.googleapis|fonts\.gstatic|cdnjs\.cloudflare|unpkg\.com|jsdelivr|images\.unsplash)#i',
                $html,
                "[{$url}] must not reference a CDN; all assets are served locally.",
            );

            $this->assertSame(
                preg_match_all('#</div>#i', $html),
                preg_match_all('/<div\b/i', $html),
                "[{$url}] has unbalanced div tags.",
            );
        }
    }

    public function test_every_configured_fallback_image_exists_on_disk(): void
    {
        $config = config('public_site');

        $paths = array_merge(
            array_values($config['department_covers']),
            array_values($config['department_art']),
            [$config['hero_fallback']],
            [$config['logo_fallback']],
            [$config['alumni_fallback']],
        );

        foreach (array_unique($paths) as $path) {
            $this->assertFileExists(
                public_path($path),
                "Fallback image [{$path}] is configured in config/public_site.php but missing from public/.",
            );
        }
    }

    public function test_public_pages_never_reference_a_missing_local_asset(): void
    {
        $urls = ['/', '/profil', '/jurusan', '/berita', '/prestasi', '/alumni', '/ppdb', '/produk-siswa', '/karier'];

        foreach ($urls as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match_all('~(?:src|href)="(/[^"?\#]+)~i', $html, $matches);

            foreach (array_unique($matches[1]) as $reference) {
                if (str_contains($reference, '/build/') || str_contains($reference, '/storage/')) {
                    continue;
                }

                $this->assertFileExists(
                    public_path(ltrim($reference, '/')),
                    "[{$url}] references missing local asset [{$reference}].",
                );
            }
        }
    }

    public function test_landing_page_renders_school_profile_from_database(): void
    {
        $profile = SchoolProfile::firstOrFail();
        $profile->update([
            'phone' => '+62 271 123456',
            'email' => 'sekolah@example.test',
            'address' => 'Jl. Pendidikan No. 1, Karanganyar',
            'website' => 'https://sekolah.example.test',
            'logo' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($profile->school_name)
            ->assertSee($profile->npsn)
            ->assertSee('href="tel:+62271123456"', false)
            ->assertSee('mailto:sekolah@example.test')
            ->assertSee('https://www.google.com/maps?q=SMK+Negeri+2+Karanganyar%2C+Jl.+Pendidikan+No.+1%2C+Karanganyar&amp;output=embed', false)
            ->assertSee('href="https://sekolah.example.test"', false)
            ->assertSee('/assets/images/logo/logo.png', false)
            ->assertSee('/assets/images/logo/logo-smk-bisa-hebat.png', false);
    }

    public function test_public_pages_fall_back_to_app_name_when_school_profile_is_missing(): void
    {
        SchoolProfile::query()->delete();

        $this->get('/')
            ->assertOk()
            ->assertSee(config('app.name'))
            ->assertSee('Lokasi sekolah belum dicantumkan.')
            ->assertDontSee('0271-6498171')
            ->assertDontSee('href="#"', false);
    }

    public function test_public_navbar_shows_profile_after_home_and_omits_ppdb_menu(): void
    {
        $response = $this->get('/profil')->assertOk();
        $html = $response->getContent();

        $homePosition = strpos($html, '>Beranda</a>');
        $profilePosition = strpos($html, '>Profil</a>');
        $departmentPosition = strpos($html, '>Jurusan</a>');

        $this->assertNotFalse($homePosition);
        $this->assertNotFalse($profilePosition);
        $this->assertNotFalse($departmentPosition);
        $this->assertLessThan($profilePosition, $homePosition);
        $this->assertLessThan($departmentPosition, $profilePosition);
        $this->assertStringNotContainsString('>PPDB</a>', $html);
    }

    public function test_school_profile_starts_with_history_and_uses_school_logo_from_profile_data(): void
    {
        $profile = SchoolProfile::firstOrFail();
        $profile->update([
            'logo' => 'school/logo-sekolah.png',
            'hero_image' => 'school/hero-placeholder.png',
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('Sejarah Sekolah')
            ->assertSee('/storage/school/logo-sekolah.png', false)
            ->assertDontSee('/storage/school/hero-placeholder.png', false)
            ->assertSee('/assets/images/logo/logo-smk-bisa-hebat.png', false);
    }

    public function test_landing_page_restores_alumni_photo_and_uses_featured_story_data(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('alumni/alumni-feature.jpg', 'image');
        $student = StudentProfile::create([
            'nis' => 'ALUMNI-001',
            'nisn' => 'ALUMNI-NISN-001',
            'full_name' => 'Alumni Unggulan',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $alumni = AlumniProfile::create([
            'student_id' => $student->id,
            'graduation_year' => 2023,
            'current_occupation' => 'Software Engineer',
            'current_company' => 'Perusahaan Contoh',
            'is_featured' => true,
        ]);
        $story = AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Membangun karier di bidang teknologi',
            'story' => 'Cerita perjalanan alumni yang tersimpan di database.',
            'career_story' => 'Berkarya di bidang teknologi setelah lulus.',
            'quote' => 'Terus belajar dan berani mencoba.',
            'is_featured' => true,
        ]);
        $story->media()->create([
            'collection' => 'default',
            'disk' => 'public',
            'path' => 'alumni/alumni-feature.jpg',
            'original_name' => 'alumni-feature.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
        ]);

        $unfeaturedStudent = StudentProfile::create([
            'nis' => 'ALUMNI-002',
            'nisn' => 'ALUMNI-NISN-002',
            'full_name' => 'Kisah Tidak Dipilih',
            'gender' => 'FEMALE',
            'status' => 'GRADUATED',
        ]);
        $unfeaturedAlumni = AlumniProfile::create([
            'student_id' => $unfeaturedStudent->id,
            'graduation_year' => 2024,
            'is_featured' => true,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $unfeaturedAlumni->id,
            'title' => 'Kisah yang Tidak Dipilih',
            'story' => 'Kisah ini belum dipilih untuk halaman publik.',
            'is_featured' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/alumni/alumni-feature.jpg', false)
            ->assertSee($story->title)
            ->assertSee($student->full_name)
            ->assertSee($story->career_story)
            ->assertSee($story->quote)
            ->assertDontSee($unfeaturedStudent->full_name)
            ->assertDontSee('Kisah yang Tidak Dipilih');
    }

    public function test_public_alumni_page_lists_only_featured_stories(): void
    {
        $featuredStudent = StudentProfile::create([
            'nis' => 'ALUMNI-PUB-001',
            'nisn' => 'ALUMNI-PUB-NISN-001',
            'full_name' => 'Alumni Terpilih Publik',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $featuredProfile = AlumniProfile::create([
            'student_id' => $featuredStudent->id,
            'graduation_year' => 2022,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $featuredProfile->id,
            'title' => 'Kisah Alumni Terpilih',
            'story' => 'Cerita ini dapat dilihat pengunjung.',
            'career_story' => 'Karier yang dipilih CMS.',
            'is_featured' => true,
        ]);

        $hiddenStudent = StudentProfile::create([
            'nis' => 'ALUMNI-PUB-002',
            'nisn' => 'ALUMNI-PUB-NISN-002',
            'full_name' => 'Alumni Tidak Terpilih',
            'gender' => 'FEMALE',
            'status' => 'GRADUATED',
        ]);
        $hiddenProfile = AlumniProfile::create([
            'student_id' => $hiddenStudent->id,
            'graduation_year' => 2023,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $hiddenProfile->id,
            'title' => 'Kisah Draf Alumni',
            'story' => 'Cerita ini tidak boleh ditampilkan.',
            'is_featured' => false,
        ]);

        $this->get('/alumni')
            ->assertOk()
            ->assertSee($featuredStudent->full_name)
            ->assertSee('Kisah Alumni Terpilih')
            ->assertSee('Karier yang dipilih CMS.')
            ->assertSee('aria-label="Alumni Terpilih Publik"', false)
            ->assertSee(route('public.alumni.show', $featuredProfile), false)
            ->assertDontSee($hiddenStudent->full_name)
            ->assertDontSee('Kisah Draf Alumni');
    }

    public function test_alumni_detail_shows_only_the_featured_story(): void
    {
        $student = StudentProfile::create([
            'nis' => 'ALUMNI-DETAIL-001',
            'nisn' => '9000000101',
            'full_name' => 'Alumni Detail',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $alumni = AlumniProfile::create([
            'student_id' => $student->id,
            'graduation_year' => 2023,
            'current_occupation' => 'Teknisi',
            'current_company' => 'Perusahaan Contoh',
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Kisah Terpilih untuk Detail',
            'story' => 'Isi kisah lengkap yang dipilih untuk publik.',
            'career_story' => 'Perjalanan karier yang terpilih.',
            'quote' => 'Tetap semangat belajar.',
            'is_featured' => true,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Kisah yang Tidak Dipilih',
            'story' => 'Isi kisah yang harus tetap tersembunyi.',
            'is_featured' => false,
        ]);

        $this->get(route('public.alumni.show', $alumni))
            ->assertOk()
            ->assertSee('Kisah Terpilih untuk Detail')
            ->assertSee('Isi kisah lengkap yang dipilih untuk publik.')
            ->assertSee('Perjalanan karier yang terpilih.')
            ->assertSee('Tetap semangat belajar.')
            ->assertDontSee('Kisah yang Tidak Dipilih')
            ->assertDontSee('Isi kisah yang harus tetap tersembunyi.');
    }

    public function test_alumni_detail_returns_not_found_without_a_featured_story(): void
    {
        $student = StudentProfile::create([
            'nis' => 'ALUMNI-DETAIL-002',
            'nisn' => '9000000102',
            'full_name' => 'Alumni Tanpa Kisah Terpilih',
            'gender' => 'FEMALE',
            'status' => 'GRADUATED',
        ]);
        $alumni = AlumniProfile::create([
            'student_id' => $student->id,
            'graduation_year' => 2022,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Kisah Draf',
            'story' => 'Kisah ini belum dipilih untuk publik.',
            'is_featured' => false,
        ]);

        $this->get(route('public.alumni.show', $alumni))->assertNotFound();
    }

    public function test_landing_page_lists_active_departments_only(): void
    {
        $active = Department::where('code', 'RPL')->firstOrFail();
        $inactive = Department::factory()->create([
            'code' => 'XYZ',
            'name' => 'Jurusan Tidak Aktif',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_department_detail_is_shown_and_inactive_department_returns_404(): void
    {
        $department = Department::where('code', 'RPL')->firstOrFail();

        $this->get("/jurusan/{$department->code}")
            ->assertOk()
            ->assertSee($department->name);

        $department->update(['is_active' => false]);

        $this->get("/jurusan/{$department->code}")->assertNotFound();
    }

    public function test_unknown_department_code_returns_404(): void
    {
        $this->get('/jurusan/TIDAK-ADA')->assertNotFound();
    }

    public function test_department_detail_lists_its_competencies(): void
    {
        $department = Department::where('code', 'RPL')->firstOrFail();

        $department->competencies()->create([
            'title' => 'Pemrograman Web',
            'description' => 'Membangun aplikasi web dinamis.',
            'sort_order' => 1,
        ]);

        $this->get("/jurusan/{$department->code}")
            ->assertOk()
            ->assertSee('Pemrograman Web');
    }

    public function test_only_published_articles_are_listed(): void
    {
        $category = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);

        $published = Article::create([
            'category_id' => $category->id,
            'author_id' => $this->author->id,
            'title' => 'Artikel Terbit',
            'slug' => 'artikel-terbit',
            'content' => 'Isi artikel terbit.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $draft = Article::create([
            'category_id' => $category->id,
            'author_id' => $this->author->id,
            'title' => 'Artikel Draf',
            'slug' => 'artikel-draf',
            'content' => 'Isi artikel draf.',
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);

        $this->get("/berita/{$published->slug}")
            ->assertOk()
            ->assertSee($published->title)
            ->assertSee(route('public.articles.index', ['category' => $category->slug]), false);

        $this->get("/berita/{$draft->slug}")->assertNotFound();
    }

    public function test_article_detail_increments_the_view_counter(): void
    {
        $category = ArticleCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $this->author->id,
            'title' => 'Artikel Populer',
            'slug' => 'artikel-populer',
            'content' => 'Isi artikel.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'views' => 0,
        ]);

        $this->get("/berita/{$article->slug}")->assertOk();

        $this->assertSame(1, $article->fresh()->views);
    }

    public function test_article_category_filter_is_submitted_and_filters_backend_results(): void
    {
        $kegiatan = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'informasi-kegiatan']);
        $pengumuman = ArticleCategory::create(['name' => 'Pengumuman', 'slug' => 'pengumuman']);

        Article::create([
            'category_id' => $kegiatan->id,
            'author_id' => $this->author->id,
            'title' => 'Kegiatan Sekolah',
            'slug' => 'kegiatan-sekolah',
            'content' => 'Isi.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        Article::create([
            'category_id' => $pengumuman->id,
            'author_id' => $this->author->id,
            'title' => 'Pengumuman Ujian',
            'slug' => 'pengumuman-ujian',
            'content' => 'Isi.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/berita?category=informasi-kegiatan')
            ->assertOk()
            ->assertSee('name="category"', false)
            ->assertSee('value="informasi-kegiatan" selected', false)
            ->assertSee('Kegiatan Sekolah')
            ->assertDontSee('Pengumuman Ujian')
            ->assertSee('name="q"', false)
            ->assertSee('type="submit"', false)
            ->assertSee('Filter');
    }

    public function test_empty_cms_tables_render_empty_states_instead_of_errors(): void
    {
        $this->get('/berita')->assertOk()->assertSee('Belum ada artikel');
        $this->get('/prestasi')->assertOk()->assertSee('Belum ada prestasi');
        $this->get('/alumni')->assertOk()->assertSee('Belum ada profil alumni');
        $this->get('/ppdb')->assertOk()->assertSee('PPDB sedang ditutup');
        $this->get('/produk-siswa')->assertOk()->assertSee('Belum ada produk');
        $this->get('/karier')->assertOk()->assertSee('Belum ada lowongan');
    }

    public function test_landing_page_skips_statistics_and_proceeds_to_departments(): void
    {
        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'program_keahlian',
            'label' => 'Statistik Aktif CMS',
            'value' => '99',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'statistik_nonaktif',
            'label' => 'Tidak Tampil',
            'value' => '999',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Statistik Aktif CMS')
            ->assertDontSee('Tidak Tampil')
            ->assertDontSee('id="ringkasan"', false)
            ->assertSeeInOrder(['id="beranda"', 'id="jurusan"'], false);
    }

    public function test_landing_page_does_not_render_student_or_graduate_statistics(): void
    {
        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'siswa_aktif',
            'label' => 'Siswa Aktif',
            'value' => '1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'lulusan_terserap',
            'label' => 'Lulusan Terserap',
            'value' => '1%',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $employed = StudentProfile::factory()->create(['status' => 'GRADUATED']);
        AlumniProfile::create([
            'student_id' => $employed->id,
            'graduation_year' => 2024,
            'current_occupation' => 'Teknisi',
        ]);

        $lookingForWork = StudentProfile::factory()->create(['status' => 'GRADUATED']);
        AlumniProfile::create([
            'student_id' => $lookingForWork->id,
            'graduation_year' => 2024,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Siswa Aktif')
            ->assertDontSee('Lulusan Terserap')
            ->assertDontSee('data-suffix="%"', false);
    }

    public function test_landing_page_does_not_render_partner_counters(): void
    {
        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'mitra_industri',
            'label' => 'Mitra Industri',
            'value' => '60+',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        CareerCompany::create(['name' => 'Mitra Satu']);
        CareerCompany::create(['name' => 'Mitra Dua']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-target="2"', false)
            ->assertDontSee('Mitra Satu')
            ->assertDontSee('Mitra Dua');
    }

    public function test_landing_page_lists_only_featured_achievements(): void
    {
        $category = AchievementCategory::create([
            'name' => 'Akademik',
            'slug' => 'akademik',
        ]);

        $featured = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Juara Olimpiade Matematika',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
            'rank' => '1',
            'organizer' => 'Dinas Pendidikan',
            'description' => 'Meraih prestasi tingkat kabupaten.',
            'is_featured' => true,
        ]);

        $latest = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Prestasi Terbaru Biasa',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-10-01',
            'is_featured' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($featured->title)
            ->assertSee(route('public.achievements.show', $featured), false)
            ->assertDontSee($latest->title);
    }

    public function test_landing_page_achievement_cards_have_four_columns(): void
    {
        $category = AchievementCategory::create([
            'name' => 'Akademik',
            'slug' => 'akademik',
        ]);

        Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Prestasi Tunggal',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
            'is_featured' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group', false)
            ->assertSee('Unggulan');
    }

    public function test_landing_page_news_grid_has_four_columns(): void
    {
        $category = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);

        Article::create([
            'category_id' => $category->id,
            'author_id' => $this->author->id,
            'title' => 'Kegiatan Sekolah Terbaru',
            'slug' => 'kegiatan-sekolah-terbaru',
            'content' => 'Isi artikel.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="beritaGrid"', false)
            ->assertSee('lg:grid-cols-4 gap-6 stagger-group', false);
    }

    public function test_achievement_detail_displays_published_achievement_information(): void
    {
        $category = AchievementCategory::create([
            'name' => 'Sains',
            'slug' => 'sains',
        ]);
        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Juara Lomba Sains',
            'scope' => 'Provinsi',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
            'rank' => 'Juara 2',
            'organizer' => 'Dinas Pendidikan',
            'description' => 'Prestasi bidang sains.',
        ]);

        $this->get(route('public.achievements.show', $achievement))
            ->assertOk()
            ->assertSee($achievement->title)
            ->assertSee($achievement->description)
            ->assertSee($achievement->organizer)
            ->assertSee(route('public.achievements.index'), false);
    }

    public function test_achievement_category_filter_uses_a_dropdown_and_filters_server_results(): void
    {
        $academic = AchievementCategory::create([
            'name' => 'Akademik',
            'slug' => 'akademik',
        ]);
        $sports = AchievementCategory::create([
            'name' => 'Olahraga',
            'slug' => 'olahraga',
        ]);
        Achievement::create([
            'achievement_category_id' => $academic->id,
            'title' => 'Juara Olimpiade',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
        ]);
        Achievement::create([
            'achievement_category_id' => $sports->id,
            'title' => 'Juara Atletik',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
        ]);

        $this->get('/prestasi?category=akademik')
            ->assertOk()
            ->assertSee('name="category"', false)
            ->assertSee('Juara Olimpiade')
            ->assertDontSee('Juara Atletik');
    }

    public function test_product_category_filter_uses_a_dropdown_and_filters_server_results(): void
    {
        $foodCategory = ProductCategory::create(['name' => 'Makanan']);
        $craftCategory = ProductCategory::create(['name' => 'Kerajinan']);
        StudentProduct::create([
            'category_id' => $foodCategory->id,
            'name' => 'Roti Siswa',
            'slug' => 'roti-siswa',
            'status' => 'AVAILABLE',
        ]);
        StudentProduct::create([
            'category_id' => $craftCategory->id,
            'name' => 'Kerajinan Siswa',
            'slug' => 'kerajinan-siswa',
            'status' => 'AVAILABLE',
        ]);

        $this->get("/produk-siswa?category={$foodCategory->id}")
            ->assertOk()
            ->assertSee('name="category"', false)
            ->assertSee('value="'.$foodCategory->id.'" selected', false)
            ->assertSee('Roti Siswa')
            ->assertDontSee('Kerajinan Siswa');
    }

    public function test_achievement_search_combines_with_category_filter_on_backend(): void
    {
        $category = AchievementCategory::create(['name' => 'Robotika', 'slug' => 'robotika']);
        $otherCategory = AchievementCategory::create(['name' => 'Bahasa', 'slug' => 'bahasa']);
        Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Tim Meraih Juara Inovasi',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
        ]);
        Achievement::create([
            'achievement_category_id' => $otherCategory->id,
            'title' => 'Juara Pidato Bahasa',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-02',
        ]);

        $this->get('/prestasi?q=Inovasi&category=robotika')
            ->assertSee('Tim Meraih Juara Inovasi')
            ->assertDontSee('Juara Pidato Bahasa')
            ->assertSee('name="q"', false)
            ->assertSee('value="Inovasi"', false)
            ->assertSee('value="robotika" selected', false);
    }

    public function test_article_search_combines_with_category_filter_on_backend(): void
    {
        $category = ArticleCategory::create(['name' => 'Kegiatan Sains', 'slug' => 'kegiatan-sains']);
        $otherCategory = ArticleCategory::create(['name' => 'Pengumuman', 'slug' => 'pengumuman']);
        Article::create([
            'category_id' => $category->id,
            'author_id' => $this->author->id,
            'title' => 'Kegiatan Laboratorium',
            'slug' => 'kegiatan-laboratorium',
            'content' => 'Siswa melakukan eksperimen sains.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);
        Article::create([
            'category_id' => $otherCategory->id,
            'author_id' => $this->author->id,
            'title' => 'Pengumuman Sekolah',
            'slug' => 'pengumuman-sekolah',
            'content' => 'Informasi untuk seluruh siswa.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/berita?q=Laboratorium&category=kegiatan-sains')
            ->assertSee('Kegiatan Laboratorium')
            ->assertDontSee('Pengumuman Sekolah')
            ->assertSee('name="category"', false)
            ->assertSee('value="kegiatan-sains" selected', false);
    }

    public function test_article_pagination_preserves_search_and_category_filters(): void
    {
        $category = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
        $otherCategory = ArticleCategory::create(['name' => 'Pengumuman', 'slug' => 'pengumuman']);

        foreach (range(1, 10) as $index) {
            Article::create([
                'category_id' => $category->id,
                'author_id' => $this->author->id,
                'title' => 'Kegiatan Keselamatan '.$index,
                'slug' => 'kegiatan-keselamatan-'.$index,
                'content' => 'Informasi kegiatan keselamatan.',
                'status' => ContentStatus::Published,
                'published_at' => now()->subMinutes($index),
            ]);
        }

        Article::create([
            'category_id' => $otherCategory->id,
            'author_id' => $this->author->id,
            'title' => 'Keselamatan Pengumuman',
            'slug' => 'keselamatan-pengumuman',
            'content' => 'Pengumuman.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/berita?q=Keselamatan&category=kegiatan&page=2')
            ->assertSee('Kegiatan Keselamatan 10')
            ->assertDontSee('Kegiatan Keselamatan 1</h3>')
            ->assertDontSee('Keselamatan Pengumuman')
            ->assertSee('q=Keselamatan', false)
            ->assertSee('category=kegiatan', false)
            ->assertSee('name="q"', false)
            ->assertSee('value="Keselamatan"', false);
    }

    public function test_product_search_combines_with_category_filter_on_backend(): void
    {
        $category = ProductCategory::create(['name' => 'Kerajinan Kayu']);
        $otherCategory = ProductCategory::create(['name' => 'Furnitur']);
        StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Rak Serbaguna',
            'slug' => 'rak-serbaguna',
            'status' => 'AVAILABLE',
        ]);
        StudentProduct::create([
            'category_id' => $otherCategory->id,
            'name' => 'Meja Belajar',
            'slug' => 'meja-belajar',
            'status' => 'AVAILABLE',
        ]);
        StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Kursi Kayu Terjual',
            'slug' => 'kursi-kayu-terjual',
            'status' => 'SOLD',
        ]);

        $this->get("/produk-siswa?q=Rak&category={$category->id}")
            ->assertSee('Rak Serbaguna')
            ->assertDontSee('Meja Belajar')
            ->assertDontSee('Kursi Kayu Terjual')
            ->assertSee('value="'.$category->id.'" selected', false);
    }

    public function test_alumni_search_filters_backend_results(): void
    {
        $graduate = StudentProfile::create([
            'nis' => 'ALUMNI-SEARCH-001',
            'nisn' => '9000000001',
            'full_name' => 'Laras Puspita',
            'gender' => 'FEMALE',
            'status' => 'GRADUATED',
        ]);
        $alumni = AlumniProfile::create([
            'student_id' => $graduate->id,
            'graduation_year' => 2022,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Perjalanan Laras',
            'story' => 'Kisah karier yang dipilih untuk publik.',
            'is_featured' => true,
        ]);

        $otherGraduate = StudentProfile::create([
            'nis' => 'ALUMNI-SEARCH-002',
            'nisn' => '9000000002',
            'full_name' => 'Bima Saputra',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $otherAlumni = AlumniProfile::create([
            'student_id' => $otherGraduate->id,
            'graduation_year' => 2021,
        ]);
        AlumniStory::create([
            'alumni_profile_id' => $otherAlumni->id,
            'title' => 'Perjalanan Bima',
            'story' => 'Kisah lain yang dipilih untuk publik.',
            'is_featured' => true,
        ]);

        $this->get('/alumni?q=Laras')
            ->assertSee('Laras Puspita')
            ->assertDontSee('Bima Saputra')
            ->assertSee('value="Laras"', false);
    }

    public function test_career_search_combines_with_type_filter_on_backend(): void
    {
        $company = CareerCompany::create(['name' => 'PT Industri Teknologi']);

        foreach (range(1, 8) as $index) {
            CareerOpportunity::create([
                'company_id' => $company->id,
                'type' => CareerOpportunityType::Job,
                'title' => 'Teknisi Industri '.$index,
                'status' => CareerOpportunityStatus::Open,
            ]);
        }
        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Staf Administrasi',
            'status' => CareerOpportunityStatus::Open,
        ]);
        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Teknisi Tertutup',
            'status' => CareerOpportunityStatus::Closed,
        ]);
        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Teknisi Magang',
            'status' => CareerOpportunityStatus::Open,
        ]);

        $this->get('/karier?q=Teknisi&type=JOB')
            ->assertSee('Teknisi Industri 1')
            ->assertDontSee('Staf Administrasi')
            ->assertDontSee('Teknisi Tertutup')
            ->assertDontSee('Teknisi Magang')
            ->assertSee('name="type"', false)
            ->assertSee('value="JOB" selected', false)
            ->assertSee('value="Teknisi"', false);
    }

    public function test_career_partner_search_filters_backend_and_preserves_query_in_pagination(): void
    {
        foreach (range(1, 7) as $index) {
            CareerCompany::create([
                'name' => 'Mitra Otomotif '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'industry' => 'Otomotif',
            ]);
        }
        CareerCompany::create([
            'name' => 'Mitra Tekstil',
            'industry' => 'Tekstil',
        ]);
        CareerCompany::create([
            'name' => 'Zeta Mitra',
            'industry' => 'Industri',
        ]);

        $this->get('/karier?company_q=Otomotif')
            ->assertSee('Mitra Otomotif 01')
            ->assertDontSee('Mitra Tekstil')
            ->assertSee('company_q=Otomotif', false)
            ->assertSee('mitra=2', false)
            ->assertSee('value="Otomotif"', false);
    }

    public function test_ppdb_page_shows_only_the_closed_state_without_closed_period_details(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        AdmissionPeriod::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'PPDB Lama',
            'registration_start' => '2026-05-01',
            'registration_end' => '2026-06-30',
            'description' => 'Informasi pendaftaran periode lama.',
            'status' => 'CLOSED',
        ]);

        $this->get('/ppdb')
            ->assertOk()
            ->assertSee('PPDB sedang ditutup')
            ->assertDontSee('PPDB Lama')
            ->assertDontSee('Informasi pendaftaran periode lama.');
    }

    public function test_open_admission_period_is_public_but_draft_is_not(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $open = AdmissionPeriod::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'PPDB Tahun Pelajaran 2026/2027',
            'registration_start' => '2026-05-01',
            'registration_end' => '2026-06-30',
            'status' => 'OPEN',
        ]);

        AdmissionPeriod::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'PPDB Belum Dipublikasikan',
            'registration_start' => '2027-05-01',
            'registration_end' => '2027-06-30',
            'status' => 'DRAFT',
        ]);

        $this->get('/ppdb')
            ->assertOk()
            ->assertSee($open->title)
            ->assertSee('Sedang dibuka')
            ->assertDontSee('PPDB Belum Dipublikasikan');
    }

    public function test_only_available_products_are_listed(): void
    {
        $category = ProductCategory::create(['name' => 'Makanan']);

        $available = StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Produk Tersedia',
            'slug' => 'produk-tersedia',
            'price' => 50000,
            'status' => 'AVAILABLE',
        ]);

        $sold = StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Produk Terjual',
            'slug' => 'produk-terjual',
            'price' => 75000,
            'status' => 'SOLD',
        ]);

        $this->get('/produk-siswa')
            ->assertOk()
            ->assertSee($available->name)
            ->assertSee(route('public.products.show', $available), false)
            ->assertDontSee('Produk Terjual');

        $this->get(route('public.products.show', $available))
            ->assertOk()
            ->assertSee($available->name)
            ->assertSee('Rp 50.000')
            ->assertSee('aria-label="Produk Tersedia"', false);

        $this->get(route('public.products.show', $sold))->assertNotFound();
    }

    public function test_landing_page_partner_marquee_leads_with_the_newest_partnership(): void
    {
        Storage::fake('public');

        $older = CareerCompany::create([
            'name' => 'PT Mitra Lama',
            'logo' => 'partners/lama.jpg',
        ]);

        $newer = CareerCompany::create([
            'name' => 'PT Mitra Baru',
            'logo' => 'partners/baru.jpg',
        ]);

        // `career_companies` has no pin column, so recency decides the order.
        $this->get('/')
            ->assertOk()
            ->assertSee('industri-marquee-track', false)
            ->assertSeeInOrder([$newer->name, $older->name]);
    }

    public function test_public_cards_render_uploaded_images_from_all_supported_sources(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('products/karya-siswa.jpg', 'image');
        Storage::disk('public')->put('news/berita.jpg', 'image');
        Storage::disk('public')->put('achievements/juara.jpg', 'image');
        Storage::disk('public')->put('partners/logo-mitra.jpg', 'image');

        $articleCategory = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
        $article = Article::create([
            'category_id' => $articleCategory->id,
            'author_id' => $this->author->id,
            'title' => 'Berita Dengan Foto',
            'slug' => 'berita-dengan-foto',
            'content' => 'Isi berita.',
            'thumbnail' => 'public/news/berita.jpg',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $productCategory = ProductCategory::create(['name' => 'Karya Siswa']);
        $product = StudentProduct::create([
            'category_id' => $productCategory->id,
            'name' => 'Produk Dengan Foto',
            'slug' => 'produk-dengan-foto',
            'status' => 'AVAILABLE',
        ]);
        $product->media()->create([
            'collection' => 'default',
            'disk' => 'local',
            'path' => 'public/storage/products/karya-siswa.jpg',
            'original_name' => 'karya-siswa.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
        ]);

        $achievementCategory = AchievementCategory::create([
            'name' => 'Akademik',
            'slug' => 'akademik',
        ]);
        $achievement = Achievement::create([
            'achievement_category_id' => $achievementCategory->id,
            'title' => 'Prestasi Dengan Foto',
            'scope' => 'Kabupaten',
            'level' => 'Siswa',
            'achievement_date' => '2026-09-01',
        ]);
        $achievement->media()->create([
            'collection' => 'default',
            'disk' => 'public',
            'path' => 'achievements/juara.jpg',
            'original_name' => 'juara.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
        ]);

        $company = CareerCompany::create([
            'name' => 'Mitra Dengan Logo',
            'logo' => 'storage/partners/logo-mitra.jpg',
            'website' => 'https://mitra.example.com',
        ]);
        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Magang Dengan Logo Mitra',
            'status' => CareerOpportunityStatus::Open,
        ]);

        $this->assertStringEndsWith(
            '/storage/news/berita.jpg',
            app(PublicMediaService::class)->url($article->thumbnail),
        );

        config([
            'app.url' => 'http://stale-app-url.test',
            'filesystems.disks.public.url' => 'http://stale-app-url.test/storage',
        ]);

        $this->get('http://school.test/')
            ->assertOk()
            ->assertSee('http://school.test/storage/products/karya-siswa.jpg')
            ->assertSee('/storage/news/berita.jpg')
            ->assertSee('/storage/partners/logo-mitra.jpg')
            ->assertSee('href="https://mitra.example.com"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false);

        $this->get('/produk-siswa')
            ->assertOk()
            ->assertSee('/storage/products/karya-siswa.jpg');

        $this->get('/berita')
            ->assertOk()
            ->assertSee('/storage/news/berita.jpg');

        $this->get('/prestasi')
            ->assertOk()
            ->assertSee('/storage/achievements/juara.jpg');

        $this->get('/karier')
            ->assertOk()
            ->assertSee('/storage/partners/logo-mitra.jpg')
            ->assertSee('Magang Dengan Logo Mitra');
    }

    public function test_career_page_lists_active_services(): void
    {
        CareerService::create([
            'title' => 'Pelatihan Karier',
            'slug' => 'pelatihan-karier',
            'description' => 'Pelatihan persiapan karier siswa.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get('/karier')
            ->assertOk()
            ->assertSee('Pelatihan Karier');
    }

    public function test_career_page_lists_only_open_opportunities_with_readable_type_labels(): void
    {
        $company = CareerCompany::create([
            'name' => 'PT Contoh Teknologi',
            'industry' => 'Teknologi Informasi',
            'logo' => 'career/companies/contoh-logo.png',
        ]);

        $internship = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Magang Backend Engineer',
            'location' => 'Jakarta',
            'close_date' => '2026-10-30',
            'status' => CareerOpportunityStatus::Open,
        ]);

        $job = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Staf Administrasi',
            'status' => CareerOpportunityStatus::Open,
        ]);

        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Lowongan Sudah Ditutup',
            'status' => CareerOpportunityStatus::Closed,
        ]);

        $this->get('/karier')
            ->assertOk()
            ->assertSee($internship->title)
            ->assertSee($job->title)
            ->assertSee('sm:grid-cols-2 xl:grid-cols-3', false)
            ->assertSee('Lihat detail')
            ->assertSee('role="img" aria-label="Staf Administrasi"', false)
            ->assertSee('PT Contoh Teknologi')
            ->assertSee('Magang')
            ->assertSee('Dibuka')
            ->assertSee('Batas pendaftaran')
            ->assertSee('aspect-[4/3] overflow-hidden bg-bluelight', false)
            ->assertSee('/storage/career/companies/contoh-logo.png', false)
            ->assertDontSee('Ditutup')
            ->assertDontSee('Lowongan Sudah Ditutup');

        $this->get('/karier?type=JOB')
            ->assertOk()
            ->assertSee('name="type"', false)
            ->assertSee($job->title)
            ->assertDontSee($internship->title)
            ->assertDontSee('Lowongan Sudah Ditutup');
    }

    public function test_career_page_paginates_the_partner_company_list(): void
    {
        foreach (range(1, 7) as $index) {
            CareerCompany::create(['name' => 'PT Mitra '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $this->get('/karier')
            ->assertOk()
            ->assertSee('PT Mitra 01')
            ->assertSee('PT Mitra 06')
            ->assertDontSee('PT Mitra 07')
            ->assertSee('mitra=2', false);

        $this->get('/karier?mitra=2')
            ->assertOk()
            ->assertSee('PT Mitra 07')
            ->assertDontSee('PT Mitra 01');
    }

    public function test_career_opportunity_card_opens_a_detail_page_with_application_information(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('career/opportunities/designer.jpg', 'image');

        $company = CareerCompany::create([
            'name' => 'PT Contoh Kreatif',
            'industry' => 'Desain',
        ]);
        $opportunity = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Desainer Grafis',
            'description' => 'Mengerjakan desain materi promosi.',
            'requirements' => 'Menguasai perangkat desain grafis.',
            'location' => 'Karanganyar',
            'open_date' => '2026-09-01',
            'close_date' => '2026-10-30',
            'application_link' => 'https://mitra.example.com/apply',
            'status' => CareerOpportunityStatus::Open,
        ]);
        $opportunity->media()->create([
            'collection' => 'photo',
            'disk' => 'public',
            'path' => 'career/opportunities/designer.jpg',
            'original_name' => 'designer.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
        ]);

        $this->get('/karier')
            ->assertOk()
            ->assertSee(route('public.career.show', $opportunity), false)
            ->assertSee('aspect-[4/3]', false)
            ->assertSee('Lihat detail')
            ->assertSee('/storage/career/opportunities/designer.jpg', false)
            ->assertSee('name="type"', false);

        $this->get(route('public.career.show', $opportunity))
            ->assertOk()
            ->assertSee('/storage/career/opportunities/designer.jpg', false)
            ->assertSee($opportunity->title)
            ->assertSee($company->name)
            ->assertSee($opportunity->description)
            ->assertSee($opportunity->requirements)
            ->assertSee('https://mitra.example.com/apply');
    }

    public function test_career_opportunity_detail_shows_an_image_placeholder_when_no_photo_exists(): void
    {
        $company = CareerCompany::create(['name' => 'Mitra PKL']);
        $opportunity = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'PKL Tanpa Foto',
            'status' => CareerOpportunityStatus::Open,
        ]);

        $this->get(route('public.career.show', $opportunity))
            ->assertOk()
            ->assertSee('aria-label="PKL Tanpa Foto"', false);
    }

    public function test_closed_career_opportunity_detail_returns_not_found(): void
    {
        $company = CareerCompany::create(['name' => 'PT Contoh']);
        $opportunity = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Magang Ditutup',
            'status' => CareerOpportunityStatus::Closed,
        ]);

        $this->get(route('public.career.show', $opportunity))->assertNotFound();
    }

    public function test_legacy_misspelled_career_url_redirects(): void
    {
        $this->get('/karir')->assertRedirect('/karier');
    }

    public function test_public_pages_render_for_guests_and_authenticated_users(): void
    {
        $this->get('/')->assertOk();

        $this->actingAs($this->author)
            ->get('/')
            ->assertOk()
            ->assertSee(route('teacher.dashboard'));
    }
}
