<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCmsArticlesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
    }

    private function getAdminUser(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('code', 'ADMIN'))->first();
    }

    public function test_admin_can_view_articles_index_page_with_pagination(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        // Create 15 articles to verify 10 per page pagination
        for ($i = 1; $i <= 15; $i++) {
            Article::create([
                'category_id' => $category->id,
                'author_id' => $admin->id,
                'title' => "Artikel Berita #{$i}",
                'slug' => "artikel-berita-{$i}",
                'excerpt' => "Ringkasan berita #{$i}",
                'content' => "Konten lengkap berita #{$i}",
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.cms.articles'));

        $response->assertOk();
        $response->assertSee('Manajemen Berita &amp; Artikel', false);
        $response->assertSee('Filter &amp; Pencarian Data Artikel', false);
        // First page should show 10 items per page
        $response->assertSee('10 data per halaman');
        $response->assertSee('Artikel Berita #15'); // Latest first
    }

    public function test_admin_can_filter_articles_by_search_and_category(): void
    {
        $admin = $this->getAdminUser();
        $catA = ArticleCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
        $catB = ArticleCategory::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        Article::create([
            'category_id' => $catA->id,
            'author_id' => $admin->id,
            'title' => 'Lomba Robotik Juara Satu',
            'slug' => 'lomba-robotik-juara-satu',
            'excerpt' => 'Robotik juara',
            'content' => 'Konten lengkap robotik',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        Article::create([
            'category_id' => $catB->id,
            'author_id' => $admin->id,
            'title' => 'Upacara Hari Pahlawan Nasional',
            'slug' => 'upacara-hari-pahlawan-nasional',
            'excerpt' => 'Upacara bendera',
            'content' => 'Konten upacara',
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ]);

        // Filter search for "Robotik"
        $responseSearch = $this->actingAs($admin)->get(route('admin.cms.articles', ['search' => 'Robotik']));
        $responseSearch->assertOk();
        $responseSearch->assertSee('Lomba Robotik Juara Satu');
        $responseSearch->assertDontSee('Upacara Hari Pahlawan Nasional');

        // Filter by category $catB
        $responseCat = $this->actingAs($admin)->get(route('admin.cms.articles', ['category_id' => $catB->id]));
        $responseCat->assertOk();
        $responseCat->assertSee('Upacara Hari Pahlawan Nasional');
        $responseCat->assertDontSee('Lomba Robotik Juara Satu');
    }

    public function test_admin_can_toggle_article_status(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Pengumuman Libur Sekolah',
            'slug' => 'pengumuman-libur-sekolah',
            'excerpt' => 'Libur semester',
            'content' => 'Konten libur',
            'status' => ContentStatus::Draft,
        ]);

        $this->assertEquals(ContentStatus::Draft, $article->status);

        // Toggle to PUBLISHED
        $response = $this->actingAs($admin)->patchJson(route('admin.cms.articles.toggle-status', $article));
        $response->assertOk();
        $response->assertJson(['success' => true, 'is_published' => true]);

        $article->refresh();
        $this->assertEquals(ContentStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
    }
}
