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

    public function test_teacher_can_create_remedial_as_draft_without_online_submission(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'REMEDIAL',
            'title' => 'Remidi Ulangan Harian 1',
            'description' => 'Tes remidi lisan / tertulis di kelas.',
            'max_score' => 75,
            'status' => 'DRAFT',
            'submission_required' => 0,
            'use_default_policy' => 1,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Remidi Ulangan Harian 1')->firstOrFail();
        $this->assertEquals(AssessmentType::Remedial, $assessment->type);
        $this->assertEquals(AssessmentStatus::Draft, $assessment->status);
        $this->assertNull($assessment->published_at);
        $this->assertFalse($assessment->submission_required);
        $this->assertNull($assessment->instructions);
    }

    public function test_teacher_can_create_quiz_published_with_submission_and_instructions(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'QUIZ',
            'title' => 'Kuis Pemahaman Logika Algoritma',
            'description' => 'Kerjakan kuis logika 10 soal.',
            'max_score' => 100,
            'status' => 'PUBLISHED',
            'submission_required' => 1,
            'instructions' => 'Unggah dokumen jawaban dalam format PDF dengan ukuran maksimal 5MB.',
            'use_default_policy' => 1,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Kuis Pemahaman Logika Algoritma')->firstOrFail();
        $this->assertEquals(AssessmentType::Quiz, $assessment->type);
        $this->assertEquals(AssessmentStatus::Published, $assessment->status);
        $this->assertNotNull($assessment->published_at);
        $this->assertTrue($assessment->submission_required);
        $this->assertEquals('Unggah dokumen jawaban dalam format PDF dengan ukuran maksimal 5MB.', $assessment->instructions);
    }

    public function test_teacher_can_publish_previously_draft_assessment(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $draft = Assessment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => AssessmentType::Project,
            'title' => 'Projek Portofolio Web Awal',
            'max_score' => 100,
            'submission_required' => true,
            'instructions' => 'Sertakan link repositori GitHub.',
            'created_by' => $this->teacherUser->teacherProfile->id,
            'published_at' => null,
            'status' => AssessmentStatus::Draft,
        ]);

        $this->assertNull($draft->published_at);

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'assessment_id' => $draft->id,
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'PROJECT',
            'title' => 'Projek Portofolio Web Final',
            'max_score' => 100,
            'status' => 'PUBLISHED',
            'submission_required' => 1,
            'instructions' => 'Sertakan link repositori GitHub dan file PDF laporan.',
            'use_default_policy' => 1,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $draft->refresh();
        $this->assertEquals('Projek Portofolio Web Final', $draft->title);
        $this->assertEquals(AssessmentType::Project, $draft->type);
        $this->assertEquals(AssessmentStatus::Published, $draft->status);
        $this->assertNotNull($draft->published_at);
        $this->assertTrue($draft->submission_required);
    }

    public function test_teacher_can_disable_late_penalty_policy(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas Tanpa Pengurangan Nilai Keterlambatan',
            'max_score' => 100,
            'enable_late_policy' => 0,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Tugas Tanpa Pengurangan Nilai Keterlambatan')->firstOrFail();
        $this->assertNotNull($assessment->latePolicy);
        $this->assertFalse((bool) $assessment->latePolicy->enabled);
    }

    public function test_new_task_defaults_to_draft_and_no_submission_required(): void
    {
        $column = $this->gradebook->columns()->firstOrFail();

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.assessments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'gradebook_column_id' => $column->id,
            'type' => 'TUGAS',
            'title' => 'Tugas Baru Default Draf',
            'max_score' => 100,
        ]);

        $response->assertRedirect(route('teacher.assessments.index', ['assignment_id' => $this->assignment->id]));

        $assessment = Assessment::where('title', 'Tugas Baru Default Draf')->firstOrFail();
        $this->assertEquals(AssessmentStatus::Draft, $assessment->status);
        $this->assertNull($assessment->published_at);
        $this->assertFalse($assessment->submission_required);
        $this->assertNotNull($assessment->latePolicy);
        $this->assertFalse((bool) $assessment->latePolicy->enabled);
    }

    public function test_ui_has_rubric_below_info_and_late_policy_at_bottom(): void
    {
        // 1. Index Page
        $resIndex = $this->actingAs($this->teacherUser)->get(route('teacher.assessments.index'));
        $resIndex->assertStatus(200);

        $contentIndex = $resIndex->getContent();
        $infoPosIndex = strpos($contentIndex, 'Batas Pengumpulan (Deadline)');
        $rubricPosIndex = strpos($contentIndex, 'Gunakan Rubrik Penilaian');
        $pubPosIndex = strpos($contentIndex, 'Status Publikasi');
        $subPosIndex = strpos($contentIndex, 'Wajibkan Pengiriman');
        $latePosIndex = strpos($contentIndex, 'Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat');

        $this->assertNotFalse($infoPosIndex, 'Info tugas should be present on index page');
        $this->assertNotFalse($rubricPosIndex, 'Rubrik form should be present on index page');
        $this->assertNotFalse($pubPosIndex, 'Status publikasi should be present on index page');
        $this->assertNotFalse($subPosIndex, 'Submission should be present on index page');
        $this->assertNotFalse($latePosIndex, 'Late policy should be present on index page');

        // Verify order: Info -> Rubrik -> Status Publikasi -> Submission -> Late Policy (at the bottom)
        $this->assertTrue($infoPosIndex < $rubricPosIndex, 'Rubrik must be below info tugas on index page');
        $this->assertTrue($rubricPosIndex < $pubPosIndex, 'Rubrik must be above Status Publikasi on index page');
        $this->assertTrue($pubPosIndex < $subPosIndex, 'Status Publikasi must be above Submission on index page');
        $this->assertTrue($subPosIndex < $latePosIndex, 'Late policy must be positioned at the very bottom on index page');

        $resIndex->assertSee('name="enable_late_policy"', false);
        $resIndex->assertSee('id="radioStatusDraft"', false);
        $resIndex->assertSee('checked', false);

        // 2. Create Page
        $resCreate = $this->actingAs($this->teacherUser)->get(route('teacher.assessments.create'));
        $resCreate->assertStatus(200);

        $contentCreate = $resCreate->getContent();
        $infoPosCreate = strpos($contentCreate, 'Batas Pengumpulan (Deadline)');
        $rubricPosCreate = strpos($contentCreate, 'Gunakan Rubrik Penilaian');
        $pubPosCreate = strpos($contentCreate, 'Status Publikasi');
        $subPosCreate = strpos($contentCreate, 'Wajibkan Pengiriman');
        $latePosCreate = strpos($contentCreate, 'Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat');

        $this->assertNotFalse($infoPosCreate, 'Info tugas should be present on create page');
        $this->assertNotFalse($rubricPosCreate, 'Rubrik form should be present on create page');
        $this->assertNotFalse($pubPosCreate, 'Status publikasi should be present on create page');
        $this->assertNotFalse($subPosCreate, 'Submission should be present on create page');
        $this->assertNotFalse($latePosCreate, 'Late policy should be present on create page');

        // Verify order: Info -> Rubrik -> Status Publikasi -> Submission -> Late Policy (at the bottom)
        $this->assertTrue($infoPosCreate < $rubricPosCreate, 'Rubrik must be below info tugas on create page');
        $this->assertTrue($rubricPosCreate < $pubPosCreate, 'Rubrik must be above Status Publikasi on create page');
        $this->assertTrue($pubPosCreate < $subPosCreate, 'Status Publikasi must be above Submission on create page');
        $this->assertTrue($subPosCreate < $latePosCreate, 'Late policy must be positioned at the very bottom on create page');

        $resCreate->assertSee('name="enable_late_policy"', false);
        $resCreate->assertSee('value="DRAFT" checked', false);
    }
}
