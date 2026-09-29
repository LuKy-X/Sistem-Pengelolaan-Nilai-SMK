<?php

namespace Tests\Feature;

use App\Models\Gradebook;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTugasFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;

    protected TeachingAssignment $assignment;

    protected Gradebook $gradebook;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->teacherUser = User::where('username', 'guru.agus')->firstOrFail();
        $this->assignment = $this->teacherUser->teacherProfile->teachingAssignments()->firstOrFail();
        $this->gradebook = $this->assignment->gradebooks()->firstOrFail();
    }

    public function test_teacher_can_access_tugas_page_with_three_tab_flow(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.assessments.index'));

        $response->assertStatus(200);

        // Header check
        $response->assertSee('Manajemen Tugas');
        $response->assertSee('Kelola Tugas Siswa');

        // Tabs check
        $response->assertSee('Daftar Kelas');
        $response->assertSee('Buku Nilai');
        $response->assertSee('Tugas/Remidi');

        // Mockup panels and form check
        $response->assertSee('Pilih kelas untuk mengelola buku nilai dan tugas/remidi');
        $response->assertSee('Daftar Nilai - Kelas');
        $response->assertSee('Manajemen Tugas/Mandiri');
        $response->assertSee('Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai');

        // Ensure "Catatan Nilai" is NOT present in the teacher sidebar
        $response->assertDontSee('<span>Catatan Nilai</span>', false);
    }

    public function test_teacher_can_submit_tugas_and_redirects_properly(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas Mandiri Pemrograman Web',
            'description' => 'Kerjakan modul studi kasus pembuatan controller.',
            'max_score' => 100,
            'due_at' => now()->addDays(5)->format('Y-m-d'),
            'enable_late_policy' => 1,
            'reduction_value' => 5,
            'interval' => 'MINGGU',
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));
        $this->assertDatabaseHas('assessments', [
            'teaching_assignment_id' => $this->assignment->id,
            'title' => 'Tugas Mandiri Pemrograman Web',
        ]);
    }
}
