<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::whereHas('roles', fn ($q) => $q->where('code', 'ADMIN'))->firstOrFail();
        $this->class = SchoolClass::where('is_active', true)->firstOrFail();
    }

    public function test_admin_can_access_attendance_index_schedule_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance.index', [
            'layout' => 'schedule',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Absensi &amp; Jurnal Kelas', false);
        $response->assertSee('Mode Admin (Read-Only)');
        $response->assertSee('Tampilan Jadwal');
        $response->assertSee('Tampilan Tabel');
        $response->assertSee('Jadwal Sesi');
        $response->assertSee('Jurnal Terisi');
        $response->assertSee('Belum Diisi Guru');
        $response->assertSee('Presensi Siswa');
    }

    public function test_admin_can_switch_to_table_layout(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance.index', [
            'layout' => 'table',
            'table_tab' => 'sessions',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rekap Sesi Jurnal');
        $response->assertSee('Catatan Siswa Lengkap');
        $response->assertSee('Materi Pembelajaran');
        $response->assertSee('Presensi (H | S | I | A)');
        $response->assertDontSee('<th class="text-center">Status</th>', false);
        $response->assertDontSee('<th class="text-center w-28">Aksi</th>', false);
        $response->assertDontSee('Detail Siswa');
    }

    public function test_admin_can_view_single_class_agenda_journal(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance.index', [
            'class_id' => $this->class->id,
            'layout' => 'schedule',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Semua Kelas');
        $response->assertSee('Agenda Jurnal &amp; Presensi: Kelas '.$this->class->name, false);
        $response->assertSee('journal-tbl', false);
        $response->assertSee('Jumlah Siswa');
        $response->assertSee('Hadir');
        $response->assertSee('Absensi');
        $response->assertDontSee('Status &amp; Aksi', false);
        $response->assertDontSee('Detail Siswa');
    }

    public function test_admin_can_filter_attendance(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance.index', [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'journal_status' => 'filled',
            'layout' => 'table',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Terapkan Filter');
    }

    public function test_admin_can_export_csv_and_excel(): void
    {
        $csvResponse = $this->actingAs($this->adminUser)->get(route('admin.attendance.export', [
            'format' => 'csv',
            'type' => 'sessions',
            'date' => now()->toDateString(),
        ]));
        $csvResponse->assertStatus(200);
        $this->assertTrue(str_contains($csvResponse->headers->get('content-type') ?? '', 'text/csv'));

        $excelResponse = $this->actingAs($this->adminUser)->get(route('admin.attendance.export', [
            'format' => 'excel',
            'type' => 'sessions',
            'date' => now()->toDateString(),
        ]));
        $excelResponse->assertStatus(200);
        $this->assertTrue(
            str_contains($excelResponse->headers->get('content-type') ?? '', 'spreadsheetml')
            || str_contains($excelResponse->headers->get('content-type') ?? '', 'text/csv')
        );
    }

    public function test_guest_cannot_access_attendance_page(): void
    {
        $response = $this->get(route('admin.attendance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_attendance_modal_rendered_with_proper_backdrop_and_no_emojis(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance.index', [
            'class_id' => $this->class->id,
            'layout' => 'schedule',
        ]));

        $response->assertStatus(200);

        // Verify modal structure has proper backdrop and unblurred dialog elevation
        $response->assertSee('id="studentAttendanceModal"', false);
        $response->assertSee('backdrop-blur-sm transition-opacity', false);
        $response->assertSee('relative z-20 pointer-events-auto bg-white', false);

        // Verify emojis are not present in the HTML response
        $content = $response->getContent();
        $this->assertFalse(str_contains($content, '✅'), 'Response contains checkmark emoji');
        $this->assertFalse(str_contains($content, '⏳'), 'Response contains hourglass emoji');
        $this->assertFalse(str_contains($content, '🕌'), 'Response contains mosque emoji');
        $this->assertFalse(str_contains($content, '☕'), 'Response contains coffee emoji');
        $this->assertFalse(str_contains($content, '🌐'), 'Response contains globe emoji');
        $this->assertFalse(str_contains($content, '📑'), 'Response contains document emoji');
        $this->assertFalse(str_contains($content, '👥'), 'Response contains people emoji');
    }
}
