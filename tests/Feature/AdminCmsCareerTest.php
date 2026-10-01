<?php

namespace Tests\Feature;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\CareerService;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsCareerTest extends TestCase
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

    public function test_admin_can_view_career_tabs(): void
    {
        $admin = $this->getAdminUser();

        $company = CareerCompany::create([
            'name' => 'PT. Astra Honda Motor',
            'industry' => 'Manufaktur Otomotif',
        ]);

        CareerOpportunity::create([
            'company_id' => $company->id,
            'title' => 'Teknisi Perakitan Sepeda Motor',
            'type' => 'JOB',
            'status' => 'OPEN',
        ]);

        CareerService::create([
            'title' => 'Bimbingan Karir & Konseling',
            'slug' => 'bimbingan-karir',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Tab opportunities
        $resOpp = $this->actingAs($admin)->get(route('admin.cms.career', ['tab' => 'opportunities']));
        $resOpp->assertOk();
        $resOpp->assertSee('Teknisi Perakitan Sepeda Motor');

        // Tab companies
        $resComp = $this->actingAs($admin)->get(route('admin.cms.career', ['tab' => 'companies']));
        $resComp->assertOk();
        $resComp->assertSee('PT. Astra Honda Motor');

        // Tab services
        $resServ = $this->actingAs($admin)->get(route('admin.cms.career', ['tab' => 'services']));
        $resServ->assertOk();
        $resServ->assertSee('Bimbingan Karir &amp; Konseling', false);
    }

    public function test_admin_can_store_and_update_career_opportunity(): void
    {
        $admin = $this->getAdminUser();
        $company = CareerCompany::create(['name' => 'PT. Telkom Indonesia', 'industry' => 'Telekomunikasi']);

        // Store
        $payload = [
            'company_id' => $company->id,
            'title' => 'Junior Network Engineer Magang',
            'type' => 'INTERNSHIP',
            'location' => 'Surakarta',
            'description' => 'Membantu pemeliharaan infrastruktur jaringan fiber optik.',
            'requirements' => 'Siswa kelas XI atau XII jurusan TKJ.',
            'open_date' => '2026-05-01',
            'close_date' => '2026-06-30',
            'application_link' => 'https://career.telkom.co.id/intern',
            'status' => 'OPEN',
        ];

        $res = $this->actingAs($admin)->post(route('admin.cms.career.opportunities.store'), $payload);
        $res->assertRedirect(route('admin.cms.career', ['tab' => 'opportunities']));
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('career_opportunities', [
            'title' => 'Junior Network Engineer Magang',
            'location' => 'Surakarta',
            'type' => 'INTERNSHIP',
            'status' => 'OPEN',
        ]);

        $opp = CareerOpportunity::where('title', 'Junior Network Engineer Magang')->first();
        $this->assertNotNull($opp);

        // Update
        $updatePayload = [
            'company_id' => $company->id,
            'title' => 'Senior Network Technician (Updated)',
            'type' => 'JOB',
            'location' => 'Semarang',
            'description' => 'Posisi karir penuh untuk alumni.',
            'requirements' => 'Lulusan SMK bidang IT/TKJ.',
            'open_date' => '2026-05-01',
            'close_date' => '2026-07-15',
            'application_link' => 'https://career.telkom.co.id/job',
            'status' => 'OPEN',
        ];

        $resUpdate = $this->actingAs($admin)->put(route('admin.cms.career.opportunities.update', $opp), $updatePayload);
        $resUpdate->assertRedirect();
        $resUpdate->assertSessionHas('success');

        $opp->refresh();
        $this->assertEquals('Senior Network Technician (Updated)', $opp->title);
        $this->assertEquals(CareerOpportunityType::Job, $opp->type);
        $this->assertEquals('Semarang', $opp->location);
    }

    public function test_admin_can_toggle_opportunity_status_via_ajax(): void
    {
        $admin = $this->getAdminUser();
        $company = CareerCompany::create(['name' => 'PT. Chemco Harapan Nusantara']);

        $opp = CareerOpportunity::create([
            'company_id' => $company->id,
            'title' => 'Operator CNC Milling',
            'type' => 'JOB',
            'status' => 'OPEN',
        ]);

        // Toggle to CLOSED
        $res = $this->actingAs($admin)->patchJson(route('admin.cms.career.opportunities.toggle-status', $opp));
        $res->assertOk();
        $res->assertJson([
            'success' => true,
            'status' => 'CLOSED',
        ]);

        $opp->refresh();
        $this->assertEquals(CareerOpportunityStatus::Closed, $opp->status);

        // Toggle back to OPEN
        $res2 = $this->actingAs($admin)->patchJson(route('admin.cms.career.opportunities.toggle-status', $opp));
        $res2->assertOk();
        $res2->assertJson([
            'success' => true,
            'status' => 'OPEN',
        ]);

        $opp->refresh();
        $this->assertEquals(CareerOpportunityStatus::Open, $opp->status);
    }

    public function test_admin_can_preview_opportunity_json(): void
    {
        $admin = $this->getAdminUser();
        $company = CareerCompany::create([
            'name' => 'PT. Solo Grafika Utama',
            'industry' => 'Percetakan & Desain',
        ]);

        $opp = CareerOpportunity::create([
            'company_id' => $company->id,
            'title' => 'Desainer Grafis Percetakan',
            'type' => 'JOB',
            'location' => 'Surakarta',
            'description' => 'Membuat tata letak buku dan materi promosi.',
            'requirements' => 'Menguasai Adobe Illustrator & Photoshop.',
            'status' => 'OPEN',
        ]);

        $res = $this->actingAs($admin)->getJson(route('admin.cms.career.opportunities.preview', $opp));
        $res->assertOk();
        $res->assertJson([
            'id' => $opp->id,
            'title' => 'Desainer Grafis Percetakan',
            'company_name' => 'PT. Solo Grafika Utama',
            'type' => 'JOB',
            'type_label' => 'Lowongan Kerja',
            'status' => 'OPEN',
        ]);
    }

    public function test_admin_can_delete_career_opportunity(): void
    {
        $admin = $this->getAdminUser();
        $company = CareerCompany::create(['name' => 'PT. Mitra Dummy']);

        $opp = CareerOpportunity::create([
            'company_id' => $company->id,
            'title' => 'Lowongan Akan Dihapus',
            'type' => 'INTERNSHIP',
            'status' => 'CLOSED',
        ]);

        $res = $this->actingAs($admin)->delete(route('admin.cms.career.opportunities.destroy', $opp));
        $res->assertRedirect();
        $res->assertSessionHas('success');

        $this->assertDatabaseMissing('career_opportunities', ['id' => $opp->id]);
    }

    public function test_admin_can_store_and_update_career_company_with_logo(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();

        $logo = UploadedFile::fake()->image('company_logo.png', 400, 400);

        $payload = [
            'name' => 'PT. Komatsu Indonesia',
            'industry' => 'Alat Berat',
            'phone' => '021-4600611',
            'email' => 'career@komatsu.co.id',
            'website' => 'https://komatsu.co.id',
            'address' => 'Jl. Raya Bekasi Km. 22, Cakung',
            'logo' => $logo,
        ];

        $res = $this->actingAs($admin)->post(route('admin.cms.career.companies.store'), $payload);
        $res->assertRedirect(route('admin.cms.career', ['tab' => 'companies']));
        $res->assertSessionHas('success');

        $company = CareerCompany::where('name', 'PT. Komatsu Indonesia')->first();
        $this->assertNotNull($company);
        $this->assertNotNull($company->logo);
        Storage::disk('public')->assertExists($company->logo);

        // Update company and replace logo
        $newLogo = UploadedFile::fake()->image('new_logo.png', 300, 300);
        $oldPath = $company->logo;

        $updatePayload = [
            'name' => 'PT. Komatsu Indonesia Tbk',
            'industry' => 'Alat Berat & Manufaktur',
            'phone' => '021-4600611',
            'email' => 'recruitment@komatsu.co.id',
            'website' => 'https://komatsu.co.id',
            'address' => 'Jl. Raya Bekasi Km. 22, Cakung, Jakarta Timur',
            'logo' => $newLogo,
        ];

        $resUpdate = $this->actingAs($admin)->put(route('admin.cms.career.companies.update', $company), $updatePayload);
        $resUpdate->assertRedirect(route('admin.cms.career', ['tab' => 'companies']));

        $company->refresh();
        $this->assertEquals('PT. Komatsu Indonesia Tbk', $company->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($company->logo);
    }

    public function test_admin_can_delete_career_company(): void
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();

        $path = 'career/companies/logo_to_delete.png';
        Storage::disk('public')->put($path, 'dummy content');

        $company = CareerCompany::create([
            'name' => 'PT. Perusahaan Dihapus',
            'logo' => $path,
        ]);

        $res = $this->actingAs($admin)->delete(route('admin.cms.career.companies.destroy', $company));
        $res->assertRedirect(route('admin.cms.career', ['tab' => 'companies']));
        $res->assertSessionHas('success');

        $this->assertDatabaseMissing('career_companies', ['id' => $company->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_store_update_toggle_and_delete_career_service(): void
    {
        $admin = $this->getAdminUser();

        // Store
        $payload = [
            'title' => 'Penyaluran Kerja BKK',
            'icon' => 'briefcase',
            'sort_order' => 2,
            'description' => 'Pendampingan administrasi dan tes seleksi kerja.',
            'content' => 'Layanan bantuan melamar ke mitra industri bagi lulusan SMK.',
            'is_active' => 1,
        ];

        $res = $this->actingAs($admin)->post(route('admin.cms.career.services.store'), $payload);
        $res->assertRedirect(route('admin.cms.career', ['tab' => 'services']));
        $res->assertSessionHas('success');

        $service = CareerService::where('title', 'Penyaluran Kerja BKK')->first();
        $this->assertNotNull($service);
        $this->assertTrue($service->is_active);

        // Update
        $updatePayload = [
            'title' => 'Penyaluran Kerja & Bursa Karir BKK',
            'icon' => 'users',
            'sort_order' => 1,
            'description' => 'Deskripsi diperbarui.',
            'content' => 'Konten diperbarui.',
            'is_active' => 1,
        ];

        $resUpdate = $this->actingAs($admin)->put(route('admin.cms.career.services.update', $service), $updatePayload);
        $resUpdate->assertRedirect(route('admin.cms.career', ['tab' => 'services']));

        $service->refresh();
        $this->assertEquals('Penyaluran Kerja & Bursa Karir BKK', $service->title);
        $this->assertEquals('users', $service->icon);

        // Toggle Status
        $resToggle = $this->actingAs($admin)->patchJson(route('admin.cms.career.services.toggle-status', $service));
        $resToggle->assertOk();
        $resToggle->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $service->refresh();
        $this->assertFalse($service->is_active);

        // Delete
        $resDelete = $this->actingAs($admin)->delete(route('admin.cms.career.services.destroy', $service));
        $resDelete->assertRedirect(route('admin.cms.career', ['tab' => 'services']));
        $this->assertDatabaseMissing('career_services', ['id' => $service->id]);
    }
}
