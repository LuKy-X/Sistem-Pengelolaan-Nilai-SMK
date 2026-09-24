<?php

namespace Tests\Feature;

use App\Enums\LateReductionType;
use App\Models\Assessment;
use App\Models\AssessmentLatePolicy;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\GradebookColumn;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\Role;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AssessmentAndRubricTest extends TestCase
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

    public function test_assessment_can_only_be_accessed_by_enrolled_students(): void
    {
        $classA = SchoolClass::factory()->create();
        $classB = SchoolClass::factory()->create();

        $assignment = TeachingAssignment::factory()->create(['class_id' => $classA->id]);
        $column = GradebookColumn::factory()->create();
        $assessment = Assessment::factory()->create([
            'teaching_assignment_id' => $assignment->id,
            'gradebook_column_id' => $column->id,
        ]);

        // Siswa 1 di kelas A
        $studentUserA = User::factory()->create();
        $studentUserA->roles()->attach(Role::where('code', 'STUDENT')->first());
        $studentProfileA = StudentProfile::factory()->create(['user_id' => $studentUserA->id]);
        ClassEnrollment::create([
            'class_id' => $classA->id,
            'student_id' => $studentProfileA->id,
            'start_date' => now()->subMonth(),
            'status' => 'ACTIVE',
        ]);

        // Siswa 2 di kelas B (kelas lain)
        $studentUserB = User::factory()->create();
        $studentUserB->roles()->attach(Role::where('code', 'STUDENT')->first());
        $studentProfileB = StudentProfile::factory()->create(['user_id' => $studentUserB->id]);
        ClassEnrollment::create([
            'class_id' => $classB->id,
            'student_id' => $studentProfileB->id,
            'start_date' => now()->subMonth(),
            'status' => 'ACTIVE',
        ]);

        // Siswa A boleh melihat assessment kelas A
        $this->assertTrue(Gate::forUser($studentUserA)->allows('view', $assessment));

        // Siswa B DILARANG melihat assessment kelas A
        $this->assertFalse(Gate::forUser($studentUserB)->allows('view', $assessment));
    }

    public function test_rubric_accumulates_criteria_points_accurately(): void
    {
        $teacher = TeacherProfile::factory()->create();
        $rubric = Rubric::create([
            'name' => 'Rubrik Pemrograman Web - Project CRUD',
            'created_by' => $teacher->id,
            'status' => 'ACTIVE',
        ]);

        // Kriteria 1: Desain UI (max 30)
        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Desain Antarmuka & Responsiveness',
            'max_points' => 30.00,
            'sort_order' => 1,
        ]);

        // Kriteria 2: Validasi & Arsitektur (max 40)
        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Validasi Form & Keamanan SQL',
            'max_points' => 40.00,
            'sort_order' => 2,
        ]);

        // Kriteria 3: Database & Eloquent (max 30)
        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Normalisasi Database & Relasi Model',
            'max_points' => 30.00,
            'sort_order' => 3,
        ]);

        $this->assertCount(3, $rubric->criteria);
        $this->assertEquals(100.00, $rubric->criteria->sum('max_points'));
    }

    public function test_late_policy_configuration_holds_correct_rules(): void
    {
        $assessment = Assessment::factory()->create(['due_at' => now()->subHours(3)]);

        $latePolicy = AssessmentLatePolicy::create([
            'assessment_id' => $assessment->id,
            'enabled' => true,
            'reduction_type' => LateReductionType::Percentage,
            'reduction_value' => 5.00, // 5% per jam
            'interval' => 60,
            'grace_period_minutes' => 15,
            'minimum_max_score' => 50.00,
        ]);

        $this->assertTrue($latePolicy->enabled);
        $this->assertEquals(LateReductionType::Percentage, $latePolicy->reduction_type);
        $this->assertEquals(5.00, $latePolicy->reduction_value);
        $this->assertEquals(50.00, $latePolicy->minimum_max_score);
    }

    public function test_journal_attendance_prevents_duplicate_student_records(): void
    {
        $teacher = TeacherProfile::factory()->create();
        $assignment = TeachingAssignment::factory()->create(['teacher_id' => $teacher->id]);
        $period = LessonPeriod::create([
            'period_number' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'label' => 'Jam 1',
        ]);

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $assignment->id,
            'journal_date' => now()->toDateString(),
            'start_period_id' => $period->id,
            'end_period_id' => $period->id,
            'material' => 'Pengenalan Framework Laravel',
            'created_by' => $teacher->id,
        ]);

        $student = StudentProfile::factory()->create();

        // Absensi pertama sukses
        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => 'PRESENT',
        ]);

        // Absensi kedua untuk siswa yang sama pada jurnal yang sama harus diblokir oleh UNIQUE constraint
        $this->expectException(QueryException::class);

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => 'SICK',
        ]);
    }
}
