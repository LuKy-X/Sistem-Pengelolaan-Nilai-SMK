<?php

namespace Tests\Feature;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Enums\ContentStatus;
use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
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
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->get('/')
            ->assertOk()
            ->assertSee($profile->school_name)
            ->assertSee($profile->npsn);
    }

    public function test_public_pages_fall_back_to_app_name_when_school_profile_is_missing(): void
    {
        SchoolProfile::query()->delete();

        $this->get('/')
            ->assertOk()
            ->assertSee(config('app.name'));
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
            ->assertSee($published->title);

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

    public function test_article_index_can_filter_by_category(): void
    {
        $kegiatan = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
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

        $this->get('/berita?kategori=kegiatan')
            ->assertOk()
            ->assertSee('Kegiatan Sekolah')
            ->assertDontSee('Pengumuman Ujian');
    }

    public function test_empty_cms_tables_render_empty_states_instead_of_errors(): void
    {
        $this->get('/berita')->assertOk()->assertSee('Belum ada artikel');
        $this->get('/prestasi')->assertOk()->assertSee('Belum ada prestasi');
        $this->get('/alumni')->assertOk()->assertSee('Belum ada profil alumni');
        $this->get('/ppdb')->assertOk()->assertSee('Belum ada periode pendaftaran');
        $this->get('/produk-siswa')->assertOk()->assertSee('Belum ada produk');
        $this->get('/karier')->assertOk()->assertSee('Belum ada lowongan');
    }

    public function test_landing_page_shows_active_hero_statistics_only(): void
    {
        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'siswa_aktif',
            'label' => 'Siswa Aktif',
            'value' => '1.234',
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
            ->assertSee('Siswa Aktif')
            ->assertSee('1.234')
            ->assertDontSee('Tidak Tampil');
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

        StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Produk Terjual',
            'slug' => 'produk-terjual',
            'price' => 75000,
            'status' => 'SOLD',
        ]);

        $this->get('/produk-siswa')
            ->assertOk()
            ->assertSee($available->name)
            ->assertDontSee('Produk Terjual');
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
        ]);

        $internship = CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Magang Backend Engineer',
            'location' => 'Jakarta',
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
            ->assertSee('PT Contoh Teknologi')
            ->assertSee('Magang')
            ->assertDontSee('Lowongan Sudah Ditutup');
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
