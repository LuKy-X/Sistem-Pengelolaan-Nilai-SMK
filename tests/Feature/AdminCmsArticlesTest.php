<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Services\PublicMediaService;
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

    public function test_public_media_service_resolves_thumbnail_path_without_type_error(): void
    {
        $service = app(PublicMediaService::class);

        // Test normal relative storage path
        $url = $service->url('articles/sample.jpg');
        $this->assertNotNull($url);
        $this->assertStringContainsString('storage/articles/sample.jpg', $url);

        // Test full URL passes through untouched
        $urlFull = $service->url('https://example.com/sample.jpg');
        $this->assertEquals('https://example.com/sample.jpg', $urlFull);
    }

    public function test_admin_can_create_article_with_manual_excerpt(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        $manualExcerpt = 'Ini adalah ringkasan berita pilihan yang diketik secara manual oleh admin.';
        $fullContent = '<p>Paragraf pertama berita yang sangat panjang dan berbeda sekali dengan ringkasan manual di atas.</p>';

        $response = $this->actingAs($admin)->post(route('admin.cms.articles.store'), [
            'category_id' => $category->id,
            'title' => 'Prestasi Siswa Juara LKS',
            'excerpt' => $manualExcerpt,
            'content' => $fullContent,
            'status' => 'PUBLISHED',
        ]);

        $response->assertRedirect(route('admin.cms.articles'));
        $this->assertDatabaseHas('articles', [
            'title' => 'Prestasi Siswa Juara LKS',
            'excerpt' => $manualExcerpt,
        ]);

        $article = Article::where('title', 'Prestasi Siswa Juara LKS')->first();
        $this->assertEquals($manualExcerpt, $article->excerpt);
    }

    public function test_admin_excerpt_validation_rejects_over_250_chars(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        $tooLongExcerpt = str_repeat('A', 251);

        $response = $this->actingAs($admin)->post(route('admin.cms.articles.store'), [
            'category_id' => $category->id,
            'title' => 'Prestasi Siswa Juara',
            'excerpt' => $tooLongExcerpt,
            'content' => '<p>Konten artikel</p>',
            'status' => 'PUBLISHED',
        ]);

        $response->assertSessionHasErrors('excerpt');
    }

    public function test_admin_can_crud_article_categories_via_ajax(): void
    {
        $admin = $this->getAdminUser();

        // 1. Create Category
        $resStore = $this->actingAs($admin)->postJson(route('admin.cms.article-categories.store'), [
            'name' => 'Inovasi & Teknologi',
        ]);
        $resStore->assertOk();
        $resStore->assertJson(['success' => true]);
        $this->assertDatabaseHas('article_categories', ['name' => 'Inovasi & Teknologi', 'slug' => 'inovasi-teknologi']);

        $category = ArticleCategory::where('slug', 'inovasi-teknologi')->first();

        // 2. Update Category
        $resUpdate = $this->actingAs($admin)->putJson(route('admin.cms.article-categories.update', $category), [
            'name' => 'Inovasi Teknologi Terapan',
        ]);
        $resUpdate->assertOk();
        $resUpdate->assertJson(['success' => true]);
        $this->assertDatabaseHas('article_categories', ['name' => 'Inovasi Teknologi Terapan', 'slug' => 'inovasi-teknologi-terapan']);

        $category->refresh();

        // 3. Delete Category (empty)
        $resDelete = $this->actingAs($admin)->deleteJson(route('admin.cms.article-categories.destroy', $category));
        $resDelete->assertOk();
        $resDelete->assertJson(['success' => true]);
        $this->assertDatabaseMissing('article_categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_that_has_articles(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Akademik', 'slug' => 'akademik']);

        Article::create([
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Ujian Akhir Semester',
            'slug' => 'ujian-akhir-semester',
            'excerpt' => 'Jadwal UAS',
            'content' => 'Konten lengkap jadwal UAS',
            'status' => ContentStatus::Published,
        ]);

        $resDelete = $this->actingAs($admin)->deleteJson(route('admin.cms.article-categories.destroy', $category));
        $resDelete->assertStatus(422);
        $resDelete->assertJson(['success' => false]);
        $this->assertDatabaseHas('article_categories', ['id' => $category->id]);
    }

    public function test_admin_article_index_renders_category_crud_modal_and_button(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.cms.articles'));

        $response->assertOk();
        $response->assertSee('Kelola Kategori');
        $response->assertSee('id="categoryCrudModal"', false);
        $response->assertSee('Tambah Kategori Baru');
    }

    public function test_admin_article_create_view_renders_quill_manual_excerpt_and_cancellation_modal(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.cms.articles.create'));

        $response->assertOk();
        $response->assertSee('quill.snow.css');
        $response->assertSee('quill.js');
        $response->assertSee('cropper.min.css');
        $response->assertSee('cropper.min.js');
        $response->assertSee('id="imageCropModal"', false);
        $response->assertSee('id="quillEditor"', false);
        $response->assertSee('Ringkasan Singkat (Lead / Excerpt)');
        $response->assertSee('maxlength="250"', false);
        $response->assertSee('id="cancelConfirmModal"', false);
        $response->assertSee('Apakah Anda yakin ingin membatalkan perubahan? Data atau tulisan yang telah Anda masukkan belum disimpan dan seluruh perubahan akan dibatalkan.');
    }

    public function test_admin_article_edit_view_renders_quill_manual_excerpt_and_cancellation_modal(): void
    {
        $admin = $this->getAdminUser();
        $category = ArticleCategory::create(['name' => 'Prestasi', 'slug' => 'prestasi']);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Judul Artikel Edit Uji Coba',
            'slug' => 'judul-artikel-edit-uji-coba',
            'excerpt' => 'Ringkasan singkat yang sudah tersimpan',
            'content' => '<p>Konten artikel yang sudah tersimpan</p>',
            'status' => ContentStatus::Draft,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cms.articles.edit', $article));

        $response->assertOk();
        $response->assertSee('quill.snow.css');
        $response->assertSee('quill.js');
        $response->assertSee('cropper.min.css');
        $response->assertSee('cropper.min.js');
        $response->assertSee('id="imageCropModal"', false);
        $response->assertSee('id="quillEditor"', false);
        $response->assertSee('Ringkasan Singkat (Lead / Excerpt)');
        $response->assertSee('Ringkasan singkat yang sudah tersimpan');
        $response->assertSee('maxlength="250"', false);
        $response->assertSee('id="cancelConfirmModal"', false);
        $response->assertSee('Apakah Anda yakin ingin membatalkan perubahan? Data atau tulisan yang telah Anda masukkan belum disimpan dan seluruh perubahan akan dibatalkan.');
    }
}
