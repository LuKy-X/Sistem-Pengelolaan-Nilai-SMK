<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassPromotionTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private AcademicYear $year1;

    private AcademicYear $year2;

    private Department $deptRpl;

    private GradeLevel $levelX;

    private GradeLevel $levelXI;

    private GradeLevel $levelXII;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        Role::firstOrCreate(['code' => 'STUDENT'], ['name' => 'Siswa']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach(Role::where('code', 'ADMIN')->first());

        $this->year1 = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->year2 = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);

        $this->deptRpl = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $this->levelX = GradeLevel::firstOrCreate(['code' => 'X'], ['name' => 'Tingkat X']);
        $this->levelXI = GradeLevel::firstOrCreate(['code' => 'XI'], ['name' => 'Tingkat XI']);
        $this->levelXII = GradeLevel::firstOrCreate(['code' => 'XII'], ['name' => 'Tingkat XII']);
    }

    public function test_admin_can_fetch_students_and_suggestions_for_promotion(): void
    {
        $classXI = SchoolClass::create([
            'academic_year_id' => $this->year1->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelXI->id,
            'code' => 'XI-RA-1',
            'name' => 'XI RA 1',
            'is_active' => true,
        ]);

        $studentA = StudentProfile::factory()->create(['full_name' => 'Ahmad Santoso', 'nis' => '10001']);
        $studentB = StudentProfile::factory()->create(['full_name' => 'Budi Pratama', 'nis' => '10002']);

        ClassEnrollment::create(['class_id' => $classXI->id, 'student_id' => $studentA->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);
        ClassEnrollment::create(['class_id' => $classXI->id, 'student_id' => $studentB->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.academic.classes.students-for-promotion', $classXI));

        $response->assertOk();
        $response->assertJson([
            'source_class' => [
                'id' => $classXI->id,
                'name' => 'XI RA 1',
                'grade_level_code' => 'XI',
            ],
            'is_graduation' => false,
            'next_level' => [
                'code' => 'XII',
            ],
            'suggested_name' => 'XII RA 1',
            'suggested_code' => 'XII-RA-1',
            'total_students' => 2,
        ]);
    }

    public function test_admin_can_promote_class_by_creating_new_higher_class(): void
    {
        // Contoh realita: XI RA 1 setelah 1 tahun dinaikkan menjadi XII RA 1
        $classXI = SchoolClass::create([
            'academic_year_id' => $this->year1->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelXI->id,
            'code' => 'XI-RA-1',
            'name' => 'XI RA 1',
            'is_active' => true,
        ]);

        $student1 = StudentProfile::factory()->create(['full_name' => 'Fajar Nugraha', 'status' => 'ACTIVE']);
        $student2 = StudentProfile::factory()->create(['full_name' => 'Gita Permata', 'status' => 'ACTIVE']);

        $enrollment1 = ClassEnrollment::create(['class_id' => $classXI->id, 'student_id' => $student1->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);
        $enrollment2 = ClassEnrollment::create(['class_id' => $classXI->id, 'student_id' => $student2->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);

        $response = $this->actingAs($this->adminUser)->post(route('admin.academic.classes.promote'), [
            'source_class_id' => $classXI->id,
            'action_type' => 'create_new',
            'new_class_name' => 'XII RA 1',
            'new_class_code' => 'XII-RA-1',
            'target_academic_year_id' => $this->year2->id,
            'student_ids' => [$student1->id, $student2->id],
            'promotion_date' => '2026-07-15',
            'deactivate_source_class' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.classes.index'));
        $response->assertSessionHas('success');

        // 1. Rombel baru XII RA 1 terbentuk
        $newClass = SchoolClass::where('code', 'XII-RA-1')->where('academic_year_id', $this->year2->id)->first();
        $this->assertNotNull($newClass);
        $this->assertEquals('XII RA 1', $newClass->name);
        $this->assertEquals($this->levelXII->id, $newClass->grade_level_id);

        // 2. Rombel asal XI RA 1 dinonaktifkan
        $this->assertFalse((bool) $classXI->fresh()->is_active);

        // 3. Status enrollment lama menjadi COMPLETED
        $this->assertEquals('COMPLETED', $enrollment1->fresh()->status);
        $this->assertEquals('2026-07-15', $enrollment1->fresh()->end_date->format('Y-m-d'));

        // 4. Status enrollment baru aktif di rombel XII
        $newEnrollment = ClassEnrollment::where('class_id', $newClass->id)->where('student_id', $student1->id)->first();
        $this->assertNotNull($newEnrollment);
        $this->assertEquals('ACTIVE', $newEnrollment->status);
        $this->assertEquals('2026-07-15', $newEnrollment->start_date->format('Y-m-d'));

        // 5. Relasi current enrollment siswa mengarah ke kelas baru XII RA 1
        $this->assertEquals($newClass->id, $student1->fresh()->currentEnrollment->class_id);
    }

    public function test_admin_can_promote_class_to_existing_higher_class(): void
    {
        $classX = SchoolClass::create([
            'academic_year_id' => $this->year1->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelX->id,
            'code' => 'X-RA-1',
            'name' => 'X RA 1',
            'is_active' => true,
        ]);

        $classXI = SchoolClass::create([
            'academic_year_id' => $this->year2->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelXI->id,
            'code' => 'XI-RA-1',
            'name' => 'XI RA 1',
            'is_active' => true,
        ]);

        $student = StudentProfile::factory()->create(['full_name' => 'Hendra Setiawan', 'status' => 'ACTIVE']);
        ClassEnrollment::create(['class_id' => $classX->id, 'student_id' => $student->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);

        $response = $this->actingAs($this->adminUser)->post(route('admin.academic.classes.promote'), [
            'source_class_id' => $classX->id,
            'action_type' => 'existing',
            'target_class_id' => $classXI->id,
            'student_ids' => [$student->id],
            'promotion_date' => '2026-07-15',
        ]);

        $response->assertRedirect(route('admin.academic.classes.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('ACTIVE', ClassEnrollment::where('class_id', $classXI->id)->where('student_id', $student->id)->value('status'));
        $this->assertEquals('COMPLETED', ClassEnrollment::where('class_id', $classX->id)->where('student_id', $student->id)->value('status'));
    }

    public function test_admin_can_graduate_grade_twelve_students(): void
    {
        $classXII = SchoolClass::create([
            'academic_year_id' => $this->year1->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-RA-1',
            'name' => 'XII RA 1',
            'is_active' => true,
        ]);

        $student = StudentProfile::factory()->create(['full_name' => 'Indah Kusuma', 'status' => 'ACTIVE']);
        $enrollment = ClassEnrollment::create(['class_id' => $classXII->id, 'student_id' => $student->id, 'start_date' => '2025-07-15', 'status' => 'ACTIVE']);

        $response = $this->actingAs($this->adminUser)->post(route('admin.academic.classes.promote'), [
            'source_class_id' => $classXII->id,
            'action_type' => 'graduate',
            'student_ids' => [$student->id],
            'promotion_date' => '2026-06-30',
        ]);

        $response->assertRedirect(route('admin.academic.classes.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('COMPLETED', $enrollment->fresh()->status);
        $this->assertEquals('GRADUATED', $student->fresh()->status);
        $this->assertEquals('2026-06-30', $student->fresh()->graduation_date->format('Y-m-d'));
    }

    public function test_guest_cannot_promote_class(): void
    {
        $class = SchoolClass::create([
            'academic_year_id' => $this->year1->id,
            'department_id' => $this->deptRpl->id,
            'grade_level_id' => $this->levelXI->id,
            'code' => 'XI-RA-1',
            'name' => 'XI RA 1',
        ]);

        $response = $this->post(route('admin.academic.classes.promote'), [
            'source_class_id' => $class->id,
            'action_type' => 'create_new',
        ]);

        $response->assertRedirect('/login');
    }
}
