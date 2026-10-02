<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSchoolProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        $teacherRole = Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->roles()->attach($teacherRole);

        SchoolProfile::create([
            'school_name' => 'SMK Negeri 2 Karanganyar',
            'npsn' => '20312345',
            'principal_name' => 'Drs. H. Sukardi, M.Pd.',
            'phone' => '(0271) 495123',
            'email' => 'info@smkn2-kra.sch.id',
            'website' => 'https://smkn2-kra.sch.id',
            'address' => 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah',
            'description' => 'Pusat keunggulan vokasi.',
            'vision' => 'Visi sekolah unggul.',
            'mission' => 'Misi sekolah bermutu.',
            'history' => 'Sejarah pendirian SMK Negeri 2 Karanganyar.',
        ]);
    }

    public function test_admin_can_view_school_profile_page_with_all_fields(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.cms.profile'));

        $response->assertStatus(200);
        $response->assertSee('Identitas &amp; Profil Sekolah', false);
        $response->assertSee('name="school_name"', false);
        $response->assertSee('name="npsn"', false);
        $response->assertSee('name="principal_name"', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="website"', false);
        $response->assertSee('name="address"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="vision"', false);
        $response->assertSee('name="mission"', false);
        $response->assertSee('name="history"', false);
        $response->assertDontSee('name="logo"', false);
        $response->assertDontSee('name="hero_image"', false);
    }

    public function test_admin_can_update_school_profile_text_fields(): void
    {
        $payload = [
            'school_name' => 'SMK Negeri 2 Karanganyar Maju',
            'npsn' => '20399999',
            'principal_name' => 'Dr. H. Ahmad Dahlan, M.Pd.',
            'phone' => '0271-998877',
            'email' => 'admin@smkn2-kra.sch.id',
            'website' => 'https://baru.smkn2-kra.sch.id',
            'address' => 'Jl. Baru No. 12 Karanganyar',
            'description' => 'Deskripsi profil sekolah terbaru dan lebih lengkap.',
            'vision' => 'Visi terbaru berdaya saing global.',
            'mission' => "1. Misi inovatif pertama.\n2. Misi kemitraan industri kedua.",
            'history' => 'Kilas sejarah pendirian sekolah dari tahun 1980 hingga era modern.',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.cms.profile.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_profile', [
            'school_name' => 'SMK Negeri 2 Karanganyar Maju',
            'npsn' => '20399999',
            'principal_name' => 'Dr. H. Ahmad Dahlan, M.Pd.',
            'description' => 'Deskripsi profil sekolah terbaru dan lebih lengkap.',
            'history' => 'Kilas sejarah pendirian sekolah dari tahun 1980 hingga era modern.',
        ]);
    }

    public function test_school_profile_update_preserves_existing_image_paths_without_table_alteration(): void
    {
        $profile = SchoolProfile::first();
        $profile->update([
            'logo' => 'school/logo.png',
            'hero_image' => 'school/hero.jpg',
        ]);

        $payload = [
            'school_name' => 'SMK Negeri 2 Karanganyar Berkarakter',
            'npsn' => '20312345',
            'principal_name' => 'Drs. H. Sukardi, M.Pd.',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.cms.profile.update'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals('school/logo.png', $profile->logo);
        $this->assertEquals('school/hero.jpg', $profile->hero_image);
        $this->assertEquals('SMK Negeri 2 Karanganyar Berkarakter', $profile->school_name);
    }

    public function test_non_admin_cannot_access_or_update_school_profile(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('admin.cms.profile'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->teacherUser)->post(route('admin.cms.profile.update'), [
            'school_name' => 'Hacked School Name',
        ]);
        $postResponse->assertStatus(403);
    }
}
