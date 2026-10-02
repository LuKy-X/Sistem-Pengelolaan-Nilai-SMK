<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Gradebook;
use App\Models\LessonPeriod;
use App\Models\StudentGradeNote;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;

    protected TeacherProfile $teacherProfile;

    protected TeachingAssignment $assignment;

    protected Gradebook $gradebook;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->teacherUser = User::where('username', 'guru.agus')->firstOrFail();
        $this->teacherProfile = $this->teacherUser->teacherProfile;
        $this->assignment = $this->teacherProfile->teachingAssignments()->firstOrFail();
        $this->gradebook = $this->assignment->gradebooks()->firstOrFail();
    }

    public function test_teacher_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Guru');
        $response->assertSee($this->teacherProfile->full_name);
    }

    public function test_guest_cannot_view_teacher_dashboard(): void
    {
        $response = $this->get(route('teacher.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_teacher_can_view_gradebooks_index(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.gradebooks.index'));

        $response->assertStatus(200);
        $response->assertSee('Buku Nilai Digital');
        $response->assertSee($this->assignment->schoolClass->name);
    }

    public function test_teacher_can_view_gradebooks_create_page(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.gradebooks.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Buku Nilai Baru');
        $response->assertSee('Struktur Kolom Penilaian');
    }

    public function test_teacher_can_create_gradebook_with_dynamic_columns(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('teacher.gradebooks.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'name' => 'Buku Nilai Pemrograman Web Gasal',
            'description' => 'Lembar nilai praktik dan teori',
            'is_active' => 1,
            'columns' => [
                [
                    'name' => 'Ulangan Harian 1',
                    'code' => 'UH1',
                    'column_type' => 'SCORE',
                    'max_score' => 100,
                    'weight' => 20,
                ],
                [
                    'name' => 'Tugas 1',
                    'code' => 'T1',
                    'column_type' => 'SCORE',
                    'max_score' => 100,
                    'weight' => 15,
                ],
                [
                    'name' => 'Rata-rata Nilai',
                    'code' => 'RATA',
                    'column_type' => 'SUMMARY',
                    'calculation_type' => 'AVERAGE',
                    'max_score' => 100,
                    'weight' => 0,
                ],
            ],
        ]);

        $newGradebook = Gradebook::where('name', 'Buku Nilai Pemrograman Web Gasal')->firstOrFail();
        $response->assertRedirect(route('teacher.gradebooks.show', $newGradebook));
        $this->assertDatabaseHas('gradebooks', [
            'id' => $newGradebook->id,
            'teaching_assignment_id' => $this->assignment->id,
        ]);
        $this->assertDatabaseHas('gradebook_columns', [
            'gradebook_id' => $newGradebook->id,
            'code' => 'UH1',
        ]);
        $this->assertDatabaseHas('gradebook_columns', [
            'gradebook_id' => $newGradebook->id,
            'code' => 'RATA',
            'column_type' => 'SUMMARY',
        ]);
    }

    public function test_teacher_can_update_and_delete_gradebook(): void
    {
        $response = $this->actingAs($this->teacherUser)->put(route('teacher.gradebooks.update', $this->gradebook), [
            'name' => 'Buku Nilai Revisi Gasal',
            'description' => 'Deskripsi diperbarui',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('teacher.gradebooks.index'));
        $this->assertDatabaseHas('gradebooks', [
            'id' => $this->gradebook->id,
            'name' => 'Buku Nilai Revisi Gasal',
        ]);

        // Delete column test
        $column = $this->gradebook->columns()->firstOrFail();
        $colDelRes = $this->actingAs($this->teacherUser)->delete(route('teacher.gradebooks.columns.destroy', [$this->gradebook, $column]));
        $colDelRes->assertRedirect();
        $this->assertDatabaseMissing('gradebook_columns', ['id' => $column->id]);

        // Delete gradebook test
        $delResponse = $this->actingAs($this->teacherUser)->delete(route('teacher.gradebooks.destroy', $this->gradebook));
        $delResponse->assertRedirect(route('teacher.gradebooks.index'));
        $this->assertDatabaseMissing('gradebooks', ['id' => $this->gradebook->id]);
    }

    public function test_teacher_can_view_gradebook_spreadsheet(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.gradebooks.show', $this->gradebook));

        $response->assertStatus(200);
        $response->assertSee('Daftar Nilai');
        $response->assertSee($this->assignment->schoolClass->name);
    }

    public function test_teacher_can_add_dynamic_column_to_gradebook(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('teacher.gradebooks.columns.store', $this->gradebook), [
            'name' => 'Ulangan Harian 3',
            'code' => 'UH3',
            'column_type' => 'SCORE',
            'max_score' => 100,
            'weight' => 15,
        ]);

        $response->assertRedirect(route('teacher.gradebooks.show', $this->gradebook));
        $this->assertDatabaseHas('gradebook_columns', [
            'gradebook_id' => $this->gradebook->id,
            'code' => 'UH3',
        ]);
    }

    public function test_teacher_can_update_scores(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();
        $student = $this->gradebook->students()->firstOrFail()->student;

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.gradebooks.scores.store', $this->gradebook), [
            'scores' => [
                $student->id => [
                    $column->id => 95,
                ],
            ],
        ]);

        $response->assertRedirect(route('teacher.gradebooks.show', $this->gradebook));
        $this->assertDatabaseHas('gradebook_scores', [
            'gradebook_column_id' => $column->id,
            'student_id' => $student->id,
            'final_score' => 95.00,
        ]);
    }

    public function test_teacher_can_create_assessment(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas 1: Analisis Kebutuhan Sistem',
            'description' => 'Kerjakan dokumen SRS sesuai format standar industri.',
            'max_score' => 100,
            'due_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'enable_late_policy' => 1,
            'reduction_value' => 5,
            'interval' => 'MINGGU',
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));
        $this->assertDatabaseHas('assessments', [
            'teaching_assignment_id' => $this->assignment->id,
            'title' => 'Tugas 1: Analisis Kebutuhan Sistem',
        ]);
    }

    public function test_teacher_can_create_rubric(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('teacher.rubrics.store'), [
            'name' => 'Rubrik Evaluasi Praktik PWPB',
            'description' => 'Pedoman penskoran modul web backend',
            'criteria' => [
                [
                    'criterion' => 'Kualitas Arsitektur MVC',
                    'description' => 'Pemisahan logic controller, model, dan view bersih',
                    'max_points' => 50,
                ],
                [
                    'criterion' => 'Validasi & Sanitasi Data',
                    'description' => 'Mencegah injeksi SQL dan XSS',
                    'max_points' => 50,
                ],
            ],
        ]);

        $response->assertRedirect(route('teacher.rubrics.index'));
        $this->assertDatabaseHas('rubrics', [
            'name' => 'Rubrik Evaluasi Praktik PWPB',
            'created_by' => $this->teacherProfile->id,
        ]);
    }

    public function test_teacher_can_store_class_journal(): void
    {
        $period5 = LessonPeriod::where('period_number', 5)->firstOrFail();
        $period6 = LessonPeriod::where('period_number', 6)->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.journals.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => now()->format('Y-m-d'),
            'start_period_id' => $period5->id,
            'end_period_id' => $period6->id,
            'material' => 'Implementasi Relasi Eloquent One-to-Many',
            'notes' => 'Siswa antusias dan menyelesaikan latihan tepat waktu.',
            'hadir_count' => 34,
            'sakit_count' => 1,
            'izin_count' => 1,
            'alpha_count' => 0,
        ]);

        $response->assertRedirect(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
            'date' => now()->format('Y-m-d'),
        ]));
        $this->assertDatabaseHas('class_journals', [
            'teaching_assignment_id' => $this->assignment->id,
            'material' => 'Implementasi Relasi Eloquent One-to-Many',
        ]);
    }

    public function test_teacher_can_create_and_delete_grade_note(): void
    {
        $student = StudentProfile::firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.grade-notes.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'student_id' => $student->id,
            'category' => 'REMEDIAL',
            'note' => 'Perlu bimbingan ulang konsep array multidimensi.',
        ]);

        $response->assertRedirect(route('teacher.grade-notes.index', ['assignment_id' => $this->assignment->id]));
        $note = StudentGradeNote::where('student_id', $student->id)->latest()->firstOrFail();
        $this->assertEquals('REMEDIAL', $note->category);

        // Delete note
        $deleteResponse = $this->actingAs($this->teacherUser)->delete(route('teacher.grade-notes.destroy', $note));
        $deleteResponse->assertRedirect(route('teacher.grade-notes.index', ['assignment_id' => $this->assignment->id]));
        $this->assertDatabaseMissing('student_grade_notes', ['id' => $note->id]);
    }

    public function test_teacher_can_update_profile(): void
    {
        $response = $this->actingAs($this->teacherUser)->put(route('teacher.profile.update'), [
            'name' => 'Agus Rum, S.Kom., M.Cs., Gr.',
            'email' => 'agus.rum@smk.test',
            'phone' => '081234567890',
            'gender' => 'L',
        ]);

        $response->assertRedirect(route('teacher.profile.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->teacherUser->id,
            'name' => 'Agus Rum, S.Kom., M.Cs., Gr.',
            'email' => 'agus.rum@smk.test',
        ]);
    }

    public function test_teacher_dashboard_pending_submission_links_to_grading_page(): void
    {
        $column = $this->gradebook->columns()->where('column_type', 'SCORE')->firstOrFail();
        $student = StudentProfile::firstOrFail();

        $assessment = Assessment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'title' => 'Tugas Dashboard Test',
            'type' => 'TASK',
            'status' => 'PUBLISHED',
            'submission_required' => true,
            'created_by' => $this->teacherProfile->id,
        ]);

        AssessmentSubmission::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
            'content' => 'Jawaban siswa',
            'submitted_at' => now(),
            'late_minutes' => 0,
        ]);

        $response = $this->actingAs($this->teacherUser)->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tugas Dashboard Test');
        $expectedUrl = route('teacher.grading.index', [
            'assignment_id' => $this->assignment->id,
            'gradebook_id' => $this->gradebook->id,
            'column_id' => $column->id,
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
        ]);
        $response->assertSee($expectedUrl);
    }
}
