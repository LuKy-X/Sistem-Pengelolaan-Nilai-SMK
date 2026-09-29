<?php

namespace Tests\Feature;

use App\Models\Gradebook;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherGradingFlowTest extends TestCase
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

    public function test_teacher_can_access_grading_page_with_three_tab_flow(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.grading.index'));

        $response->assertStatus(200);

        // Header and tab checks
        $response->assertSee('Penilaian Siswa');
        $response->assertSee('Daftar Kelas');
        $response->assertSee('Buku Nilai');
        $response->assertSee('Penilaian');

        // Form Penilaian check
        $response->assertSee('Penilaian Tugas/Mandiri');
        $response->assertSee('Kriteria &amp; Rincian Penilaian', false);
        $response->assertSee('Simpan Nilai');
    }

    public function test_teacher_can_submit_grading_score(): void
    {
        $column = $this->gradebook->columns()->where('column_type', 'SCORE')->firstOrFail();
        $student = StudentProfile::firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.grading.store'), [
            'gradebook_column_id' => $column->id,
            'student_id' => $student->id,
            'final_score' => 95,
            'rubric_items' => [
                'Dijabarkan Cara Pengerjaannya' => 45,
                'Jawaban Benar & Rapi' => 40,
                'Ketepatan Waktu & Kejujuran' => 10,
            ],
            'teacher_notes' => 'Pengerjaan sangat rapi dan lengkap.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gradebook_scores', [
            'gradebook_column_id' => $column->id,
            'student_id' => $student->id,
            'final_score' => 95.00,
        ]);
    }

    public function test_teacher_sees_gradebooks_grouped_per_class(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.gradebooks.index'));

        $response->assertStatus(200);
        $response->assertSee('Buku Nilai Digital');
        $response->assertSee('Filter Rombel:');
        $response->assertSee('Semua Kelas');
        $response->assertSee($this->assignment->schoolClass->name);
        $response->assertSee('Buat Buku Nilai Baru');
    }

    public function test_teacher_can_create_new_gradebook_with_horizontal_columns(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.gradebooks.create'));

        $response->assertStatus(200);
        $response->assertSee('Lembar Buku Nilai');
        $response->assertSee('Struktur Kolom Penilaian');
        $response->assertSee('Pengaturan Kolom:');
        $response->assertSee('Preset Standar SMK');

        // Post create gradebook
        $postResponse = $this->actingAs($this->teacherUser)->post(route('teacher.gradebooks.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'name' => 'Buku Nilai Tambahan Kelas Test',
            'is_active' => 1,
            'description' => 'Testing interactive column builder',
            'columns' => [
                [
                    'name' => 'Tugas Mandiri 1',
                    'code' => 'TM1',
                    'column_type' => 'SCORE',
                    'calculation_type' => 'AVERAGE',
                    'weight' => 20,
                    'max_score' => 100,
                ],
                [
                    'name' => 'Ulangan Harian 1',
                    'code' => 'UH1',
                    'column_type' => 'SCORE',
                    'calculation_type' => 'AVERAGE',
                    'weight' => 30,
                    'max_score' => 100,
                ],
            ],
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('gradebooks', [
            'teaching_assignment_id' => $this->assignment->id,
            'name' => 'Buku Nilai Tambahan Kelas Test',
        ]);
        $this->assertDatabaseHas('gradebook_columns', [
            'code' => 'TM1',
            'name' => 'Tugas Mandiri 1',
        ]);
    }
}
