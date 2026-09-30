<?php

namespace Tests\Feature;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TeachingAssignmentAndGradebookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        Role::firstOrCreate(['code' => 'STUDENT'], ['name' => 'Siswa']);
        Role::firstOrCreate(['code' => 'COUNSELOR'], ['name' => 'BK']);
    }

    public function test_teacher_can_have_multiple_teaching_assignments_across_multiple_classes(): void
    {
        $teacher = TeacherProfile::factory()->create();
        $classA = SchoolClass::factory()->create(['code' => 'XII-RPL-1']);
        $classB = SchoolClass::factory()->create(['code' => 'XII-RPL-2']);
        $classC = SchoolClass::factory()->create(['code' => 'XI-RPL-1']);

        $subjectMtk = Subject::factory()->create(['code' => 'MTK']);
        $subjectPbo = Subject::factory()->create(['code' => 'PBO']);

        $semester = Semester::factory()->create();

        // Guru Agus mengajar MTK di XII RPL 1
        $assign1 = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectMtk->id,
            'class_id' => $classA->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        // Guru Agus mengajar MTK di XII RPL 2
        $assign2 = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectMtk->id,
            'class_id' => $classB->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        // Guru Agus mengajar PBO di XI RPL 1
        $assign3 = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectPbo->id,
            'class_id' => $classC->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 6,
        ]);

        $this->assertCount(3, $teacher->teachingAssignments);
        $this->assertEquals($classA->id, $assign1->class_id);
        $this->assertEquals($classB->id, $assign2->class_id);
        $this->assertEquals($classC->id, $assign3->class_id);
    }

    public function test_teacher_can_have_multiple_gradebooks_with_dynamic_columns(): void
    {
        $assignment = TeachingAssignment::factory()->create();

        // 1. Guru membuat 2 buku nilai untuk 1 teaching assignment
        $gradebook1 = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Teori',
        ]);

        $gradebook2 = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Praktik Portofolio',
        ]);

        $this->assertCount(2, $assignment->gradebooks);

        // 2. Menambahkan banyak kolom dinamis pada gradebook 1
        $col1 = GradebookColumn::create([
            'gradebook_id' => $gradebook1->id,
            'name' => 'UH 1 - Eksponen',
            'code' => 'UH1',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100.00,
            'sort_order' => 1,
        ]);

        $col2 = GradebookColumn::create([
            'gradebook_id' => $gradebook1->id,
            'name' => 'UH 2 - Logaritma',
            'code' => 'UH2',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100.00,
            'sort_order' => 2,
        ]);

        $col3 = GradebookColumn::create([
            'gradebook_id' => $gradebook1->id,
            'name' => 'Rata-rata UH',
            'code' => 'AVG_UH',
            'column_type' => GradebookColumnType::Summary,
            'sort_order' => 3,
        ]);

        $this->assertCount(3, $gradebook1->columns);
        $this->assertEquals('UH1', $col1->code);
        $this->assertEquals(GradebookColumnType::Summary, $col3->column_type);
    }

    public function test_student_cannot_have_duplicate_score_for_the_same_gradebook_column(): void
    {
        $column = GradebookColumn::factory()->create();
        $student = StudentProfile::factory()->create();

        // Nilai pertama berhasil disimpan
        GradebookScore::create([
            'gradebook_column_id' => $column->id,
            'student_id' => $student->id,
            'raw_score' => 85.00,
            'final_score' => 85.00,
        ]);

        // Upaya insert kedua untuk siswa yang sama pada kolom yang sama harus gagal oleh Unique constraint
        $this->expectException(QueryException::class);

        GradebookScore::create([
            'gradebook_column_id' => $column->id,
            'student_id' => $student->id,
            'raw_score' => 90.00,
            'final_score' => 90.00,
        ]);
    }

    public function test_teacher_cannot_update_another_teachers_gradebook(): void
    {
        $teacherUserA = User::factory()->create();
        $teacherUserA->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfileA = TeacherProfile::factory()->create(['user_id' => $teacherUserA->id]);

        $teacherUserB = User::factory()->create();
        $teacherUserB->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfileB = TeacherProfile::factory()->create(['user_id' => $teacherUserB->id]);

        $assignmentA = TeachingAssignment::factory()->create(['teacher_id' => $teacherProfileA->id]);
        $gradebookA = Gradebook::factory()->create(['teaching_assignment_id' => $assignmentA->id]);

        // Guru A boleh mengupdate gradebook miliknya
        $this->assertTrue(Gate::forUser($teacherUserA)->allows('update', $gradebookA));

        // Guru B DILARANG mengupdate gradebook milik Guru A
        $this->assertFalse(Gate::forUser($teacherUserB)->allows('update', $gradebookA));
    }

    public function test_teacher_can_export_gradebook_as_excel_spreadsheet(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfile = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        $assignment = TeachingAssignment::factory()->create(['teacher_id' => $teacherProfile->id]);
        $gradebook = Gradebook::factory()->create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Rekap Test',
        ]);

        GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'Tugas 1',
            'code' => 'T1',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100,
            'weight' => 20,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($teacherUser)
            ->get(route('teacher.gradebooks.export', $gradebook));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_teacher_cannot_export_another_teachers_gradebook(): void
    {
        $teacherUserA = User::factory()->create();
        $teacherUserA->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfileA = TeacherProfile::factory()->create(['user_id' => $teacherUserA->id]);

        $teacherUserB = User::factory()->create();
        $teacherUserB->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfileB = TeacherProfile::factory()->create(['user_id' => $teacherUserB->id]);

        $assignmentA = TeachingAssignment::factory()->create(['teacher_id' => $teacherProfileA->id]);
        $gradebookA = Gradebook::factory()->create(['teaching_assignment_id' => $assignmentA->id]);

        $response = $this->actingAs($teacherUserB)
            ->get(route('teacher.gradebooks.export', $gradebookA));

        $response->assertForbidden();
    }

    public function test_teacher_can_configure_summary_column_sources_and_reorder_columns(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfile = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        $assignment = TeachingAssignment::factory()->create(['teacher_id' => $teacherProfile->id]);

        // 1. Create gradebook with columns and explicit summary sources
        $postData = [
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Kurikulum Merdeka',
            'is_active' => 1,
            'columns' => [
                [
                    'name' => 'Ulangan Harian 1',
                    'code' => 'UH1',
                    'column_type' => 'SCORE',
                    'weight' => 15,
                    'max_score' => 100,
                ],
                [
                    'name' => 'Ulangan Harian 2',
                    'code' => 'UH2',
                    'column_type' => 'SCORE',
                    'weight' => 15,
                    'max_score' => 100,
                ],
                [
                    'name' => 'Rata-rata UH',
                    'code' => 'RUH',
                    'column_type' => 'SUMMARY',
                    'calculation_type' => 'AVERAGE',
                    'weight' => 30,
                    'max_score' => 100,
                    'sources' => ['UH1', 'UH2'],
                ],
                [
                    'name' => 'Tugas 1',
                    'code' => 'T1',
                    'column_type' => 'SCORE',
                    'weight' => 10,
                    'max_score' => 100,
                ],
            ],
        ];

        $response = $this->actingAs($teacherUser)
            ->post(route('teacher.gradebooks.store'), $postData);

        $gradebook = Gradebook::where('name', 'Buku Nilai Kurikulum Merdeka')->first();
        $this->assertNotNull($gradebook);
        $response->assertRedirect(route('teacher.gradebooks.show', $gradebook));

        $ruhCol = $gradebook->columns()->where('code', 'RUH')->first();
        $this->assertNotNull($ruhCol);
        $this->assertCount(2, $ruhCol->sourceColumns);
        $this->assertEquals(['UH1', 'UH2'], $ruhCol->sourceColumns->pluck('code')->sort()->values()->all());

        // 2. Update gradebook: Reorder columns (Geser T1 ke depan RUH) and update sources
        $uh1 = $gradebook->columns()->where('code', 'UH1')->first();
        $uh2 = $gradebook->columns()->where('code', 'UH2')->first();
        $t1 = $gradebook->columns()->where('code', 'T1')->first();

        $updateData = [
            'name' => 'Buku Nilai Kurikulum Merdeka (Revisi Urutan)',
            'columns' => [
                [
                    'id' => $uh1->id,
                    'name' => 'Ulangan Harian 1',
                    'code' => 'UH1',
                    'column_type' => 'SCORE',
                    'weight' => 15,
                    'max_score' => 100,
                ],
                [
                    'id' => $t1->id,
                    'name' => 'Tugas 1 (Digeser ke Kiri)',
                    'code' => 'T1',
                    'column_type' => 'SCORE',
                    'weight' => 10,
                    'max_score' => 100,
                ],
                [
                    'id' => $uh2->id,
                    'name' => 'Ulangan Harian 2',
                    'code' => 'UH2',
                    'column_type' => 'SCORE',
                    'weight' => 15,
                    'max_score' => 100,
                ],
                [
                    'id' => $ruhCol->id,
                    'name' => 'Rata-rata UH',
                    'code' => 'RUH',
                    'column_type' => 'SUMMARY',
                    'calculation_type' => 'AVERAGE',
                    'weight' => 30,
                    'max_score' => 100,
                    'sources' => ['UH1', 'UH2'],
                ],
            ],
        ];

        $updateResponse = $this->actingAs($teacherUser)
            ->put(route('teacher.gradebooks.update', $gradebook), $updateData);

        $updateResponse->assertRedirect(route('teacher.gradebooks.index'));

        // Refresh columns order
        $orderedCols = $gradebook->columns()->orderBy('sort_order')->get();
        $this->assertEquals('UH1', $orderedCols[0]->code);
        $this->assertEquals('T1', $orderedCols[1]->code);
        $this->assertEquals('UH2', $orderedCols[2]->code);
        $this->assertEquals('RUH', $orderedCols[3]->code);
    }

    public function test_gradebook_show_and_export_dynamically_calculates_summary_scores(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->roles()->attach(Role::where('code', 'TEACHER')->first());
        $teacherProfile = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        $assignment = TeachingAssignment::factory()->create(['teacher_id' => $teacherProfile->id]);
        $gradebook = Gradebook::factory()->create(['teaching_assignment_id' => $assignment->id]);

        $student = StudentProfile::factory()->create();
        $gradebook->students()->create(['student_id' => $student->id]);

        $uh1 = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'UH 1',
            'code' => 'UH1',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100,
            'weight' => 20,
            'sort_order' => 1,
        ]);

        $uh2 = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'UH 2',
            'code' => 'UH2',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100,
            'weight' => 20,
            'sort_order' => 2,
        ]);

        $ruh = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'Rata-rata UH',
            'code' => 'RUH',
            'column_type' => GradebookColumnType::Summary,
            'calculation_type' => GradebookCalculationType::Average,
            'max_score' => 100,
            'weight' => 40,
            'sort_order' => 3,
        ]);

        $ruh->sourceColumns()->sync([$uh1->id, $uh2->id]);

        // Student scores: 80 and 90 -> avg should be 85.0
        GradebookScore::create([
            'gradebook_column_id' => $uh1->id,
            'student_id' => $student->id,
            'final_score' => 80,
            'raw_score' => 80,
        ]);

        GradebookScore::create([
            'gradebook_column_id' => $uh2->id,
            'student_id' => $student->id,
            'final_score' => 90,
            'raw_score' => 90,
        ]);

        $response = $this->actingAs($teacherUser)
            ->get(route('teacher.gradebooks.show', $gradebook));

        $response->assertOk();
        $scoresMatrix = $response->viewData('scoresMatrix');
        $this->assertEquals(85.0, $scoresMatrix[$student->id][$ruh->id]);

        // Export test
        $exportResponse = $this->actingAs($teacherUser)
            ->get(route('teacher.gradebooks.export', $gradebook));
        $exportResponse->assertOk();
    }
}
