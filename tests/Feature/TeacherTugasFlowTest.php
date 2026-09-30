<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\Gradebook;
use App\Models\Rubric;
use App\Models\RubricCriterion;
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
        $response->assertSessionHas('selected_gradebook_id', $column->gradebook_id);
        $response->assertSessionHas('selected_column_id', $column->id);
        $this->assertDatabaseHas('assessments', [
            'teaching_assignment_id' => $this->assignment->id,
            'title' => 'Tugas Mandiri Pemrograman Web',
        ]);
    }

    public function test_teacher_can_submit_tugas_with_default_late_policy(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas dengan Aturan Keterlambatan Default',
            'description' => 'Tugas menggunakan aturan default 5 poin per minggu.',
            'max_score' => 100,
            'due_at' => now()->addDays(7)->format('Y-m-d'),
            'enable_late_policy' => 1,
            'use_default_policy' => 1,
            // Even if custom values were passed, use_default_policy overrides them to 5.00 and 7
            'reduction_value' => 20,
            'interval' => 'HARI',
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Tugas dengan Aturan Keterlambatan Default')->firstOrFail();
        $this->assertNotNull($assessment->latePolicy);
        $this->assertEquals(5.00, (float) $assessment->latePolicy->reduction_value);
        $this->assertEquals(7, $assessment->latePolicy->interval);
    }

    public function test_teacher_can_submit_tugas_with_custom_late_policy(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas dengan Aturan Kustom',
            'max_score' => 100,
            'enable_late_policy' => 1,
            'use_default_policy' => 0,
            'reduction_value' => 10,
            'interval' => 'HARI',
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Tugas dengan Aturan Kustom')->firstOrFail();
        $this->assertNotNull($assessment->latePolicy);
        $this->assertEquals(10.00, (float) $assessment->latePolicy->reduction_value);
        $this->assertEquals(1, $assessment->latePolicy->interval);
    }

    public function test_teacher_can_submit_tugas_with_rubric(): void
    {
        $rubric = Rubric::create([
            'name' => 'Rubrik Proyek Laravel',
            'description' => 'Pedoman penskoran proyek',
            'created_by' => $this->teacherUser->teacherProfile->id,
            'status' => 'DRAFT',
        ]);

        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Struktur MVC',
            'description' => 'Pemisahan controller dan model rapi',
            'max_points' => 50,
            'sort_order' => 1,
        ]);

        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas Berbasis Rubrik',
            'max_score' => 50,
            'use_rubric' => 1,
            'rubric_id' => $rubric->id,
            'use_default_policy' => 1,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Tugas Berbasis Rubrik')->firstOrFail();
        $this->assertEquals($rubric->id, $assessment->rubric_id);
    }

    public function test_teacher_can_update_existing_task_for_column(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $existing = Assessment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => AssessmentType::Task,
            'title' => 'Tugas Lama',
            'max_score' => 80,
            'submission_required' => true,
            'created_by' => $this->teacherUser->teacherProfile->id,
            'published_at' => now(),
            'status' => AssessmentStatus::Published,
        ]);

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'assessment_id' => $existing->id,
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas Diperbarui',
            'description' => 'Deskripsi baru',
            'max_score' => 100,
            'use_default_policy' => 1,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $existing->refresh();
        $this->assertEquals('Tugas Diperbarui', $existing->title);
        $this->assertEquals(100, (float) $existing->max_score);
    }
}
