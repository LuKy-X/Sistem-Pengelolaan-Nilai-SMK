<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\StudentProduct;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsProductsTest extends TestCase
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

    public function test_admin_can_view_products_index_with_pagination(): void
    {
        $admin = $this->getAdminUser();
        $category = ProductCategory::create(['name' => 'Kreatif Digital']);

        for ($i = 1; $i <= 15; $i++) {
            StudentProduct::create([
                'category_id' => $category->id,
                'name' => "Produk Kreatif #{$i}",
                'slug' => "produk-kreatif-{$i}",
                'price' => 50000 * $i,
                'status' => 'AVAILABLE',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.cms.products'));

        $response->assertOk();
        $response->assertSee('Katalog Produk &amp; Jasa Kreatif Siswa', false);
        $response->assertSee('Produk Kreatif #1');
    }

    public function test_admin_can_filter_products_by_category_and_status(): void
    {
        $admin = $this->getAdminUser();
        $cat1 = ProductCategory::create(['name' => 'Rekayasa Perangkat Lunak']);
        $cat2 = ProductCategory::create(['name' => 'Kriya Tekstil']);

        StudentProduct::create([
            'category_id' => $cat1->id,
            'name' => 'Sistem Informasi Sekolah',
            'slug' => 'sistem-informasi-sekolah',
            'price' => 500000,
            'status' => 'AVAILABLE',
        ]);

        StudentProduct::create([
            'category_id' => $cat2->id,
            'name' => 'Batik Tulis Corak Kontemporer',
            'slug' => 'batik-tulis-corak-kontemporer',
            'price' => 175000,
            'status' => 'PRE_ORDER',
        ]);

        // Filter by category
        $responseCat = $this->actingAs($admin)->get(route('admin.cms.products', ['category_id' => $cat1->id]));
        $responseCat->assertOk();
        $responseCat->assertSee('Sistem Informasi Sekolah');
        $responseCat->assertDontSee('Batik Tulis Corak Kontemporer');

        // Filter by status
        $responseStat = $this->actingAs($admin)->get(route('admin.cms.products', ['status' => 'PRE_ORDER']));
        $responseStat->assertOk();
        $responseStat->assertSee('Batik Tulis Corak Kontemporer');
        $responseStat->assertDontSee('Sistem Informasi Sekolah');
    }

    public function test_admin_can_store_product_with_photo_and_students(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = ProductCategory::create(['name' => 'Teknologi Informasi']);
        $student = StudentProfile::factory()->create();

        $photo = UploadedFile::fake()->image('iot_box.jpg', 600, 400);

        $payload = [
            'category_id' => $category->id,
            'name' => 'Perangkat Smart Greenhouse IoT',
            'price' => 750000,
            'contact' => '081234567890',
            'description' => 'Kontrol kelembapan dan suhu tanaman otomatis berbasis ESP32.',
            'status' => 'AVAILABLE',
            'photo' => $photo,
            'student_ids' => [$student->id],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cms.products.store'), $payload);

        $response->assertRedirect(route('admin.cms.products'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('student_products', [
            'name' => 'Perangkat Smart Greenhouse IoT',
            'price' => 750000,
            'status' => 'AVAILABLE',
        ]);

        $product = StudentProduct::where('name', 'Perangkat Smart Greenhouse IoT')->first();
        $this->assertNotNull($product);

        // Verify polymorphic media
        $this->assertCount(1, $product->media);
        $media = $product->media->first();
        $this->assertEquals('photo', $media->collection);
        Storage::disk('public')->assertExists($media->path);

        // Verify pivot students
        $this->assertDatabaseHas('product_students', [
            'product_id' => $product->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_admin_can_update_product_and_replace_photo(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = ProductCategory::create(['name' => 'Teknologi']);

        $product = StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Mesin CNC Mini',
            'slug' => 'mesin-cnc-mini',
            'price' => 1200000,
            'status' => 'AVAILABLE',
        ]);

        $newPhoto = UploadedFile::fake()->image('cnc_new.png', 800, 600);

        $updatePayload = [
            'category_id' => $category->id,
            'name' => 'Mesin CNC Mini V2',
            'price' => 1500000,
            'contact' => '08987654321',
            'description' => 'Versi pembaruan dengan akurasi lebih presisi.',
            'status' => 'PRE_ORDER',
            'photo' => $newPhoto,
        ];

        $response = $this->actingAs($admin)->put(route('admin.cms.products.update', $product), $updatePayload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Mesin CNC Mini V2', $product->name);
        $this->assertEquals('1500000.00', (string) $product->price);
        $this->assertEquals('PRE_ORDER', $product->status);
        $this->assertCount(1, $product->media);
        Storage::disk('public')->assertExists($product->media->first()->path);
    }

    public function test_admin_can_preview_product_json(): void
    {
        $admin = $this->getAdminUser();
        $category = ProductCategory::create(['name' => 'Otomotif']);

        $product = StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Helm Pintar Anti-Maling',
            'slug' => 'helm-pintar-anti-maling',
            'price' => 350000,
            'status' => 'AVAILABLE',
            'contact' => '08123456789',
            'description' => 'Dilengkapi sensor sidik jari dan GPS tracker.',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.cms.products.preview', $product));

        $response->assertOk();
        $response->assertJson([
            'id' => $product->id,
            'name' => 'Helm Pintar Anti-Maling',
            'category_name' => 'Otomotif',
            'status' => 'AVAILABLE',
            'status_label' => 'Tersedia (Ready)',
            'formatted_price' => 'Rp 350.000',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = ProductCategory::create(['name' => 'Umum']);

        $product = StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Produk Akan Dihapus',
            'slug' => 'produk-akan-dihapus',
            'price' => 10000,
            'status' => 'OUT_OF_STOCK',
        ]);

        $path = 'products/delete_sample.jpg';
        Storage::disk('public')->put($path, 'dummy content');
        $media = $product->media()->create([
            'collection' => 'photo',
            'disk' => 'public',
            'path' => $path,
            'original_name' => 'delete_sample.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.cms.products.destroy', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('student_products', ['id' => $product->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
