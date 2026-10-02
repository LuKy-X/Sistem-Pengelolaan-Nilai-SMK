<?php

namespace Tests\Feature;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\GradebookStudent;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGradesMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        Role::firstOrCreate(['code' => 'STUDENT'], ['name' => 'Siswa']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach(Role::where('code', 'ADMIN')->first());

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->roles()->attach(Role::where('code', 'TEACHER')->first());
    }

    public function test_admin_can_access_grades_monitoring_index(): void
    {
        $teacher = TeacherProfile::factory()->create(['user_id' => $this->teacherUser->id]);
        $class = SchoolClass::factory()->create(['name' => 'XII RPL 1']);
        $subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
        $semester = Semester::factory()->create(['name' => 'Semester Gasal']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        $gradebook = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai PWPB XII',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.grades.index'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Buku Nilai');
        $response->assertSee('Buku Nilai PWPB XII');
        $response->assertSee('XII RPL 1');
        $response->assertSee('Lihat Nilai');
    }

    public function test_admin_can_view_gradebook_with_column_concept_read_only(): void
    {
        $teacher = TeacherProfile::factory()->create(['user_id' => $this->teacherUser->id]);
        $class = SchoolClass::factory()->create(['name' => 'XII RPL 1']);
        $subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
        $semester = Semester::factory()->create(['name' => 'Semester Gasal']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        $gradebook = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Harian PWPB',
            'is_active' => true,
        ]);

        // Kolom 1: UH 1 (Score)
        $col1 = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'Ulangan Harian 1',
            'code' => 'UH1',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100.00,
            'weight' => 30.00,
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        // Kolom 2: UH 2 (Score)
        $col2 = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'Ulangan Harian 2',
            'code' => 'UH2',
            'column_type' => GradebookColumnType::Score,
            'max_score' => 100.00,
            'weight' => 30.00,
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        // Kolom 3: Rata-rata UH (Summary)
        $col3 = GradebookColumn::create([
            'gradebook_id' => $gradebook->id,
            'name' => 'Rata-rata UH',
            'code' => 'RATA',
            'column_type' => GradebookColumnType::Summary,
            'calculation_type' => GradebookCalculationType::Average,
            'sort_order' => 3,
            'is_visible' => true,
        ]);
        $col3->sourceColumns()->sync([$col1->id, $col2->id]);

        // Siswa
        $studentUser = User::factory()->create(['name' => 'Budi Santoso']);
        $studentUser->roles()->attach(Role::where('code', 'STUDENT')->first());
        $student = StudentProfile::factory()->create([
            'user_id' => $studentUser->id,
            'full_name' => 'Budi Santoso',
            'nis' => '10293847',
        ]);

        GradebookStudent::create([
            'gradebook_id' => $gradebook->id,
            'student_id' => $student->id,
        ]);

        // Scores
        GradebookScore::create([
            'gradebook_column_id' => $col1->id,
            'student_id' => $student->id,
            'raw_score' => 80.00,
            'final_score' => 80.00,
        ]);

        GradebookScore::create([
            'gradebook_column_id' => $col2->id,
            'student_id' => $student->id,
            'raw_score' => 90.00,
            'final_score' => 90.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.grades.show', $gradebook));

        $response->assertStatus(200);
        $response->assertSee('Buku Nilai Harian PWPB');
        $response->assertSee('Mode Monitoring (Hanya Lihat)');
        $response->assertSee('UH1');
        $response->assertSee('UH2');
        $response->assertSee('RATA');
        $response->assertSee('Budi Santoso');
        $response->assertSee('10293847');
        $response->assertSee('80');
        $response->assertSee('90');
        $response->assertSee('85'); // Dynamically computed average: (80 + 90) / 2
        $response->assertSee('Rata-rata Kelas:');
        $response->assertSee('Export Nilai');
        $response->assertSee('Export ke PDF');
        $response->assertSee('Export ke Excel');

        // Verify that teacher editing buttons are NOT present in read-only admin view
        $response->assertDontSee('Atur Kolom');
        $response->assertDontSee('Buka Penilaian');
        $response->assertDontSee('Manajemen Tugas');
    }

    public function test_admin_can_export_gradebook_to_excel(): void
    {
        $teacher = TeacherProfile::factory()->create(['user_id' => $this->teacherUser->id]);
        $class = SchoolClass::factory()->create(['name' => 'X RPL 1']);
        $subject = Subject::factory()->create(['name' => 'Dasar Pemrograman']);
        $semester = Semester::factory()->create(['name' => 'Semester Gasal']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        $gradebook = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Dasar Pemrograman',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.grades.export.excel', $gradebook));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('content-type'));
        $response->assertSee('REKAPITULASI BUKU NILAI PESERTA DIDIK');
        $response->assertSee('Dasar Pemrograman');
    }

    public function test_admin_can_export_gradebook_to_pdf_preview(): void
    {
        $teacher = TeacherProfile::factory()->create(['user_id' => $this->teacherUser->id]);
        $class = SchoolClass::factory()->create(['name' => 'X RPL 2']);
        $subject = Subject::factory()->create(['name' => 'Basis Data']);
        $semester = Semester::factory()->create(['name' => 'Semester Gasal']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'weekly_hours' => 4,
        ]);

        $gradebook = Gradebook::create([
            'teaching_assignment_id' => $assignment->id,
            'name' => 'Buku Nilai Basis Data',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.grades.export.pdf', $gradebook));

        $response->assertOk();
        $response->assertSee('REKAPITULASI BUKU NILAI SISWA');
        $response->assertSee('X RPL 2');
        $response->assertSee('Basis Data');
    }

    public function test_guest_cannot_access_admin_grades(): void
    {
        $response = $this->get(route('admin.grades.index'));
        $response->assertRedirect('/login');
    }
}
