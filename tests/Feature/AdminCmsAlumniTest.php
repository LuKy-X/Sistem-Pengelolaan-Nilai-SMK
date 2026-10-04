<?php

namespace Tests\Feature;

use App\Models\AlumniProfile;
use App\Models\AlumniStory;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsAlumniTest extends TestCase
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
        return User::whereHas('roles', fn ($query) => $query->where('code', 'ADMIN'))->firstOrFail();
    }

    public function test_admin_can_manage_alumni_story_and_feature_it_on_public_homepage(): void
    {
        Storage::fake('public');

        $admin = $this->getAdminUser();
        $student = StudentProfile::create([
            'nis' => 'ALUMNI-CMS-001',
            'nisn' => 'ALUMNI-CMS-NISN-001',
            'full_name' => 'Alumni Dikelola CMS',
            'gender' => 'FEMALE',
            'status' => 'GRADUATED',
        ]);
        $alumni = AlumniProfile::create([
            'student_id' => $student->id,
            'graduation_year' => 2021,
            'current_occupation' => 'Staf',
            'is_featured' => false,
        ]);
        $story = AlumniStory::create([
            'alumni_profile_id' => $alumni->id,
            'title' => 'Kisah Lama',
            'story' => 'Cerita lama.',
            'is_featured' => false,
        ]);
        $photo = UploadedFile::fake()->image('alumni-featured.png', 600, 750);

        $this->actingAs($admin)
            ->get(route('admin.cms.alumni.index'))
            ->assertOk()
            ->assertSee('Alumni Dikelola CMS')
            ->assertSee('Kisah alumni')
            ->assertSee('role="dialog"', false)
            ->assertSee('data-alumni=', false)
            ->assertSee('aria-label="Edit data Alumni Dikelola CMS"', false);

        $this->actingAs($admin)
            ->put(route('admin.cms.alumni.update', $student), [
                'graduation_year' => 2021,
                'current_occupation' => 'Pengembang Perangkat Lunak',
                'current_company' => 'PT Contoh Teknologi',
                'city' => 'Karanganyar',
                'social_link' => 'https://example.test/alumni',
                'is_featured' => '1',
                'story' => [
                    'title' => 'Dari Sekolah ke Dunia Kerja',
                    'story' => 'Kisah alumni yang dikelola lewat CMS.',
                    'career_story' => 'Memulai karier setelah lulus.',
                    'quote' => 'Terus belajar dan berkembang.',
                    'photo' => $photo,
                ],
            ])
            ->assertRedirect(route('admin.cms.alumni.index'))
            ->assertSessionHas('success');

        $alumni->refresh();
        $story->refresh();
        $media = $story->media()->firstOrFail();

        $this->assertTrue($alumni->is_featured);
        $this->assertSame('Pengembang Perangkat Lunak', $alumni->current_occupation);
        $this->assertSame('Dari Sekolah ke Dunia Kerja', $story->title);
        $this->assertTrue($story->is_featured);
        $this->assertModelExists($media);
        Storage::disk('public')->assertExists($media->path);

        $this->get('/')
            ->assertOk()
            ->assertSee($story->title)
            ->assertSee($student->full_name)
            ->assertSee('/storage/'.$media->path, false);

        $this->actingAs($admin)
            ->put(route('admin.cms.alumni.update', $student), [
                'graduation_year' => 2021,
                'current_occupation' => 'Pengembang Perangkat Lunak',
                'current_company' => 'PT Contoh Teknologi',
                'city' => 'Karanganyar',
                'social_link' => 'https://example.test/alumni',
                'is_featured' => '0',
                'story' => [
                    'title' => 'Dari Sekolah ke Dunia Kerja',
                    'story' => 'Kisah alumni yang dikelola lewat CMS.',
                    'career_story' => 'Memulai karier setelah lulus.',
                    'quote' => 'Terus belajar dan berkembang.',
                ],
            ])
            ->assertRedirect(route('admin.cms.alumni.index'));

        $this->assertDatabaseHas('alumni_stories', [
            'id' => $story->id,
            'is_featured' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Dari Sekolah ke Dunia Kerja')
            ->assertDontSee('Alumni Dikelola CMS');
    }

    public function test_admin_can_add_a_story_to_a_graduate_alumni_profile(): void
    {
        $admin = $this->getAdminUser();
        $student = StudentProfile::create([
            'nis' => 'ALUMNI-CMS-002',
            'nisn' => 'ALUMNI-CMS-NISN-002',
            'full_name' => 'Alumni Tanpa Cerita',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $this->actingAs($admin)
            ->put(route('admin.cms.alumni.update', $student), [
                'graduation_year' => 2020,
                'current_occupation' => 'Teknisi',
                'current_company' => 'Industri Contoh',
                'city' => 'Surakarta',
                'is_featured' => '1',
                'story' => [
                    'title' => 'Langkah Pertama',
                    'story' => 'Cerita pengalaman setelah lulus sekolah.',
                ],
            ])
            ->assertRedirect(route('admin.cms.alumni.index'));

        $this->assertDatabaseHas('alumni_stories', [
            'alumni_profile_id' => AlumniProfile::where('student_id', $student->id)->value('id'),
            'title' => 'Langkah Pertama',
            'story' => 'Cerita pengalaman setelah lulus sekolah.',
            'is_featured' => true,
        ]);
    }

    public function test_admin_alumni_list_includes_graduates_without_an_alumni_profile(): void
    {
        $admin = $this->getAdminUser();
        $graduate = StudentProfile::create([
            'nis' => 'ALUMNI-CMS-003',
            'nisn' => 'ALUMNI-CMS-NISN-003',
            'full_name' => 'Lulusan Belum Dikelola',
            'gender' => 'MALE',
            'status' => 'GRADUATED',
        ]);
        $currentStudent = StudentProfile::create([
            'nis' => 'SISWA-AKTIF-001',
            'nisn' => 'SISWA-AKTIF-NISN-001',
            'full_name' => 'Siswa Masih Aktif',
            'gender' => 'FEMALE',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cms.alumni.index'))
            ->assertOk()
            ->assertSee($graduate->full_name)
            ->assertSee(route('admin.cms.alumni.update', $graduate), false)
            ->assertSee('<table', false)
            ->assertSee('aria-label="Isi data Lulusan Belum Dikelola"', false)
            ->assertDontSee($currentStudent->full_name);
    }

    public function test_admin_cannot_publish_a_student_who_has_not_graduated(): void
    {
        $admin = $this->getAdminUser();
        $student = StudentProfile::create([
            'nis' => 'SISWA-AKTIF-002',
            'nisn' => 'SISWA-AKTIF-NISN-002',
            'full_name' => 'Siswa Belum Lulus',
            'gender' => 'FEMALE',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.cms.alumni.update', $student), [
                'graduation_year' => 2026,
                'is_featured' => '1',
                'story' => [
                    'title' => 'Kisah yang tidak boleh terbit',
                    'story' => 'Siswa ini belum lulus.',
                ],
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('alumni_profiles', ['student_id' => $student->id]);
    }
}
