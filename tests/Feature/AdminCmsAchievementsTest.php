<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsAchievementsTest extends TestCase
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

    public function test_admin_can_view_achievements_index_page_with_pagination(): void
    {
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'LKS Vokasi', 'slug' => 'lks-vokasi']);

        // Create 15 achievements to test 10 per page pagination
        for ($i = 1; $i <= 15; $i++) {
            Achievement::create([
                'achievement_category_id' => $category->id,
                'title' => "Prestasi Kejuaraan #{$i}",
                'scope' => 'VOKASI',
                'level' => 'NASIONAL',
                'achievement_date' => now()->subDays($i)->format('Y-m-d'),
                'organizer' => 'Kemendikbud',
                'rank' => 'Juara 1',
                'description' => "Deskripsi prestasi kejuaraan #{$i}",
                'is_featured' => $i === 1,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.cms.achievements'));

        $response->assertOk();
        $response->assertSee('Etalase Prestasi Sekolah &amp; Siswa', false);
        $response->assertSee('10 data per halaman');
        $response->assertSee('Prestasi Kejuaraan #1');
    }

    public function test_admin_achievement_photos_resolve_legacy_storage_paths_and_disks(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Robotika', 'slug' => 'robotika']);
        $path = 'achievements/legacy-photo.jpg';

        Storage::disk('public')->put($path, 'image');

        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Prestasi Dengan Foto Legacy',
            'scope' => 'VOKASI',
            'level' => 'NASIONAL',
            'achievement_date' => '2026-06-15',
        ]);
        $achievement->media()->create([
            'collection' => 'photo',
            'disk' => 'local',
            'path' => 'public/storage/'.$path,
            'original_name' => 'legacy-photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cms.achievements'))
            ->assertOk()
            ->assertSee('/storage/'.$path)
            ->assertDontSee('/storage/public/storage/'.$path);
    }

    public function test_admin_can_filter_achievements(): void
    {
        $admin = $this->getAdminUser();
        $cat1 = AchievementCategory::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        $cat2 = AchievementCategory::create(['name' => 'Olahraga', 'slug' => 'olahraga']);

        Achievement::create([
            'achievement_category_id' => $cat1->id,
            'title' => 'Juara Web Design Nasional',
            'scope' => 'VOKASI',
            'level' => 'NASIONAL',
            'achievement_date' => '2026-03-10',
            'organizer' => 'BPTI Kemendikbud',
            'rank' => 'Juara 1',
            'is_featured' => true,
        ]);

        Achievement::create([
            'achievement_category_id' => $cat2->id,
            'title' => 'Lomba Lari Cepat Provinsi',
            'scope' => 'NON_AKADEMIK',
            'level' => 'PROVINSI',
            'achievement_date' => '2026-02-15',
            'organizer' => 'Dispora',
            'rank' => 'Juara 2',
            'is_featured' => false,
        ]);

        // Filter by category
        $response = $this->actingAs($admin)->get(route('admin.cms.achievements', ['category_id' => $cat1->id]));
        $response->assertOk();
        $response->assertSee('Juara Web Design Nasional');
        $response->assertDontSee('Lomba Lari Cepat Provinsi');

        // Filter by level
        $responseLvl = $this->actingAs($admin)->get(route('admin.cms.achievements', ['level' => 'PROVINSI']));
        $responseLvl->assertOk();
        $responseLvl->assertSee('Lomba Lari Cepat Provinsi');
        $responseLvl->assertDontSee('Juara Web Design Nasional');

        // Filter by is_featured
        $responseFeat = $this->actingAs($admin)->get(route('admin.cms.achievements', ['is_featured' => '1']));
        $responseFeat->assertOk();
        $responseFeat->assertSee('Juara Web Design Nasional');
        $responseFeat->assertDontSee('Lomba Lari Cepat Provinsi');
    }

    public function test_admin_can_store_achievement_with_photo_and_students(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Sains & Riset', 'slug' => 'sains-riset']);

        $student = StudentProfile::factory()->create();

        $photo = UploadedFile::fake()->image('trophy.jpg', 600, 400);

        $payload = [
            'achievement_category_id' => $category->id,
            'title' => 'Olimpiade Sains Nasional Informatika',
            'scope' => 'AKADEMIK',
            'level' => 'NASIONAL',
            'achievement_date' => '2026-04-12',
            'organizer' => 'Puspresnas',
            'rank' => 'Medali Emas',
            'description' => 'Mendapatkan nilai sempurna pada problem solving algoritma.',
            'is_featured' => 1,
            'photo' => $photo,
            'student_ids' => [$student->id],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cms.achievements.store'), $payload);

        $response->assertRedirect(route('admin.cms.achievements'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('achievements', [
            'title' => 'Olimpiade Sains Nasional Informatika',
            'rank' => 'Medali Emas',
            'is_featured' => true,
        ]);

        $achievement = Achievement::where('title', 'Olimpiade Sains Nasional Informatika')->first();
        $this->assertNotNull($achievement);

        // Verify polymorphic media
        $this->assertCount(1, $achievement->media);
        $media = $achievement->media->first();
        $this->assertEquals('photo', $media->collection);
        Storage::disk('public')->assertExists($media->path);

        // Verify participant
        $this->assertDatabaseHas('achievement_participants', [
            'achievement_id' => $achievement->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_admin_can_update_achievement_and_replace_photo(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Vokasi', 'slug' => 'vokasi']);

        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'LKS Cloud Computing 2025',
            'scope' => 'VOKASI',
            'level' => 'PROVINSI',
            'achievement_date' => '2025-11-20',
            'organizer' => 'Dinas Pendidikan Jateng',
            'rank' => 'Juara 2',
            'is_featured' => false,
        ]);

        $newPhoto = UploadedFile::fake()->image('new_trophy.png', 800, 600);

        $updatePayload = [
            'achievement_category_id' => $category->id,
            'title' => 'LKS Cloud Computing 2026 (Updated)',
            'scope' => 'VOKASI',
            'level' => 'NASIONAL',
            'achievement_date' => '2026-05-10',
            'organizer' => 'Kemendikbudristek',
            'rank' => 'Juara 1',
            'description' => 'Berhasil melaju ke tingkat nasional',
            'is_featured' => 1,
            'photo' => $newPhoto,
        ];

        $response = $this->actingAs($admin)->put(route('admin.cms.achievements.update', $achievement), $updatePayload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $achievement->refresh();
        $this->assertEquals('LKS Cloud Computing 2026 (Updated)', $achievement->title);
        $this->assertEquals('NASIONAL', $achievement->level);
        $this->assertTrue($achievement->is_featured);
        $this->assertCount(1, $achievement->media);
        Storage::disk('public')->assertExists($achievement->media->first()->path);
    }

    public function test_admin_can_toggle_pin_achievement_via_ajax(): void
    {
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Prestasi Catur Pelajar',
            'scope' => 'NON_AKADEMIK',
            'level' => 'KABUPATEN',
            'achievement_date' => '2026-01-10',
            'is_featured' => false,
        ]);

        // Pin it
        $response = $this->actingAs($admin)->patchJson(route('admin.cms.achievements.toggle-pin', $achievement));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_featured' => true,
        ]);

        $achievement->refresh();
        $this->assertTrue($achievement->is_featured);

        // Unpin it
        $responseUnpin = $this->actingAs($admin)->patchJson(route('admin.cms.achievements.toggle-pin', $achievement));
        $responseUnpin->assertOk();
        $responseUnpin->assertJson([
            'success' => true,
            'is_featured' => false,
        ]);

        $achievement->refresh();
        $this->assertFalse($achievement->is_featured);
    }

    public function test_admin_can_fetch_preview_json(): void
    {
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Robotika', 'slug' => 'robotika']);

        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Kontes Robot Indonesia 2026',
            'scope' => 'VOKASI',
            'level' => 'NASIONAL',
            'achievement_date' => '2026-06-15',
            'organizer' => 'Kemendikbud',
            'rank' => 'Juara 1 Terbaik',
            'description' => 'Desain robot cerdas pemadam api.',
            'is_featured' => true,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.cms.achievements.preview', $achievement));

        $response->assertOk();
        $response->assertJson([
            'id' => $achievement->id,
            'title' => 'Kontes Robot Indonesia 2026',
            'category_name' => 'Robotika',
            'scope' => 'VOKASI',
            'level' => 'NASIONAL',
            'rank' => 'Juara 1 Terbaik',
            'is_featured' => true,
        ]);
    }

    public function test_admin_can_delete_achievement(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $category = AchievementCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        $achievement = Achievement::create([
            'achievement_category_id' => $category->id,
            'title' => 'Prestasi yang akan dihapus',
            'scope' => 'AKADEMIK',
            'level' => 'KABUPATEN',
            'achievement_date' => '2026-01-01',
            'is_featured' => false,
        ]);

        // Attach a photo to media
        $path = 'achievements/to_delete.jpg';
        Storage::disk('public')->put($path, 'dummy content');
        $media = $achievement->media()->create([
            'collection' => 'photo',
            'disk' => 'public',
            'path' => $path,
            'original_name' => 'to_delete.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.cms.achievements.destroy', $achievement));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('achievements', ['id' => $achievement->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
