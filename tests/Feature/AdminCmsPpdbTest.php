<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionFeeItem;
use App\Models\AdmissionPath;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionRequirement;
use App\Models\AdmissionScheduleItem;
use App\Models\User;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\AdmissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCmsPpdbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(AcademicYearSeeder::class);
        $this->seed(AdmissionSeeder::class);
    }

    private function getAdminUser(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('code', 'ADMIN'))->first();
    }

    public function test_admin_can_view_ppdb_index_page(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.cms.ppdb'));

        $response->assertOk();
        $response->assertSee('Penerimaan Peserta Didik Baru (PPDB)');
        $response->assertSee('Kelola Gelombang');
    }

    public function test_admin_can_store_new_admission_period(): void
    {
        $admin = $this->getAdminUser();
        $year = AcademicYear::first();

        $response = $this->actingAs($admin)->post(route('admin.cms.ppdb.periods.store'), [
            'academic_year_id' => $year->id,
            'title' => 'PPDB Gelombang 2 - Jalur Reguler',
            'registration_start' => '2026-07-01',
            'registration_end' => '2026-07-31',
            'description' => 'Pendaftaran gelombang kedua.',
            'status' => 'OPEN',
        ]);

        $period = AdmissionPeriod::where('title', 'PPDB Gelombang 2 - Jalur Reguler')->first();
        $this->assertNotNull($period);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', $period));
    }

    public function test_admin_can_view_period_manage_page(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->get(route('admin.cms.ppdb.periods.manage', $period));

        $response->assertOk();
        $response->assertSee($period->title);
        $response->assertSee('Informasi Gelombang');
        $response->assertSee('Jadwal Pendaftaran');
        $response->assertSee('Jalur Pendaftaran');
        $response->assertSee('Persyaratan');
        $response->assertSee('Biaya Pendaftaran');
    }

    public function test_admin_can_update_admission_period_info(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->put(route('admin.cms.ppdb.periods.update', $period), [
            'academic_year_id' => $period->academic_year_id,
            'title' => 'PPDB Gelombang 1 Terupdate',
            'registration_start' => '2026-06-05',
            'registration_end' => '2026-07-20',
            'description' => 'Deskripsi gelombang yang telah diubah.',
            'status' => 'OPEN',
        ]);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'period']));

        $this->assertDatabaseHas('admission_periods', [
            'id' => $period->id,
            'title' => 'PPDB Gelombang 1 Terupdate',
            'registration_start' => '2026-06-05 00:00:00',
        ]);
    }

    public function test_admin_can_update_and_reorder_schedules_with_swap(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        // Submit schedules in new swapped order, with an added schedule item
        $response = $this->actingAs($admin)->put(route('admin.cms.ppdb.periods.schedules.update', $period), [
            'schedules' => [
                [
                    'id' => null,
                    'title' => 'Sosialisasi & Pendaftaran Awal',
                    'start_date' => '2026-06-01',
                    'end_date' => '2026-06-10',
                    'description' => 'Sosialisasi ke sekolah-sekolah SMP.',
                ],
                [
                    'id' => null,
                    'title' => 'Verifikasi Berkas & Fisik',
                    'start_date' => '2026-06-11',
                    'end_date' => '2026-06-25',
                    'description' => 'Pemeriksaan kelengkapan berkas fisik.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'schedules']));

        $items = AdmissionScheduleItem::where('admission_period_id', $period->id)
            ->orderBy('step_number')
            ->get();

        $this->assertCount(2, $items);
        $this->assertSame('Sosialisasi & Pendaftaran Awal', $items[0]->title);
        $this->assertSame(1, $items[0]->step_number);
        $this->assertSame(1, $items[0]->sort_order);

        $this->assertSame('Verifikasi Berkas & Fisik', $items[1]->title);
        $this->assertSame(2, $items[1]->step_number);
        $this->assertSame(2, $items[1]->sort_order);
    }

    public function test_admin_can_update_paths(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->put(route('admin.cms.ppdb.periods.paths.update', $period), [
            'paths' => [
                [
                    'id' => null,
                    'name' => 'Jalur Zonasi Khusus',
                    'quota' => 150,
                    'is_active' => '1',
                    'description' => 'Radius terdekat dari domisili sekolah.',
                ],
                [
                    'id' => null,
                    'name' => 'Jalur Prestasi Tahfidz',
                    'quota' => 20,
                    'is_active' => '1',
                    'description' => 'Minimal hafalan 3 juz.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'paths']));

        $paths = AdmissionPath::where('admission_period_id', $period->id)->orderBy('sort_order')->get();
        $this->assertCount(2, $paths);
        $this->assertSame('Jalur Zonasi Khusus', $paths[0]->name);
        $this->assertSame('jalur-zonasi-khusus', $paths[0]->slug);
        $this->assertSame(150, $paths[0]->quota);
        $this->assertTrue($paths[0]->is_active);
    }

    public function test_admin_can_update_requirements(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->put(route('admin.cms.ppdb.periods.requirements.update', $period), [
            'requirements' => [
                [
                    'id' => null,
                    'title' => 'Fotokopi Akta Kelahiran dan Kartu Keluarga',
                    'description' => 'Sebanyak 2 lembar.',
                ],
                [
                    'id' => null,
                    'title' => 'Surat Bebas Narkoba',
                    'description' => 'Dari klinik atau rumah sakit resmi.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'requirements']));

        $reqs = AdmissionRequirement::where('admission_period_id', $period->id)->orderBy('sort_order')->get();
        $this->assertCount(2, $reqs);
        $this->assertSame('Fotokopi Akta Kelahiran dan Kartu Keluarga', $reqs[0]->title);
        $this->assertSame(1, $reqs[0]->sort_order);
    }

    public function test_admin_can_update_fees_with_free_toggle(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->put(route('admin.cms.ppdb.periods.fees.update', $period), [
            'fees' => [
                [
                    'id' => null,
                    'name' => 'Biaya Pendaftaran Online',
                    'amount' => 50000,
                    'is_free' => '0',
                    'description' => 'Biaya administrasi map & formulir.',
                ],
                [
                    'id' => null,
                    'name' => 'Biaya SPP Bulan Pertama',
                    'amount' => 0,
                    'is_free' => '1',
                    'description' => 'Gratis program beasiswa sekolah.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'fees']));

        $fees = AdmissionFeeItem::where('admission_period_id', $period->id)->orderBy('sort_order')->get();
        $this->assertCount(2, $fees);

        $this->assertSame('Biaya Pendaftaran Online', $fees[0]->name);
        $this->assertEquals(50000.00, (float) $fees[0]->amount);
        $this->assertFalse((bool) $fees[0]->is_free);

        $this->assertSame('Biaya SPP Bulan Pertama', $fees[1]->name);
        $this->assertEquals(0.00, (float) $fees[1]->amount);
        $this->assertTrue((bool) $fees[1]->is_free);
    }

    public function test_public_ppdb_page_reflects_updated_data(): void
    {
        $period = AdmissionPeriod::first();
        $period->update([
            'title' => 'PPDB SMK Hebat 2026/2027',
            'status' => 'OPEN',
        ]);

        $period->scheduleItems()->delete();
        $period->scheduleItems()->create([
            'title' => 'Tahap Pertama Ujian Bakat Siswa',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'step_number' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->get('/ppdb');

        $response->assertOk();
        $response->assertSee('PPDB SMK Hebat 2026/2027');
        $response->assertSee('Tahap Pertama Ujian Bakat Siswa');
    }

    public function test_admin_can_destroy_period(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();

        $response = $this->actingAs($admin)->delete(route('admin.cms.ppdb.periods.destroy', $period));

        $response->assertRedirect(route('admin.cms.ppdb'));
        $this->assertDatabaseMissing('admission_periods', ['id' => $period->id]);
    }

    public function test_admin_can_toggle_period_status(): void
    {
        $admin = $this->getAdminUser();
        $period = AdmissionPeriod::first();
        $initialStatus = $period->status;
        $expectedStatus = $initialStatus === 'OPEN' ? 'CLOSED' : 'OPEN';

        $response = $this->actingAs($admin)->patchJson(route('admin.cms.ppdb.periods.toggle-status', $period));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => $expectedStatus,
            'is_open' => $expectedStatus === 'OPEN',
        ]);

        $this->assertSame($expectedStatus, $period->fresh()->status);
    }
}
