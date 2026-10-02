<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDropdownEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        $teacherRole = Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->roles()->attach($teacherRole);

        TeacherProfile::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198501012010011001',
            'full_name' => 'Budi Santoso, M.Kom',
            'gender' => 'MALE',
            'is_active' => true,
        ]);

        $year = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        Semester::create([
            'academic_year_id' => $year->id,
            'name' => 'Semester 1 Ganjil',
            'semester_number' => 1,
            'type' => 'ODD',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $dept = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $level = GradeLevel::create([
            'code' => 'XI',
            'name' => 'Tingkat XI',
            'order' => 2,
        ]);

        SchoolClass::create([
            'academic_year_id' => $year->id,
            'department_id' => $dept->id,
            'grade_level_id' => $level->id,
            'code' => 'XI-RPL-1',
            'name' => 'XI RPL 1',
            'is_active' => true,
        ]);
    }

    public function test_admin_pages_include_dropdown_enhancement_assets(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('assets/css/admin-dropdowns.css');
        $response->assertSee('assets/js/admin-dropdowns.js');
    }

    public function test_non_admin_pages_do_not_include_admin_dropdown_assets(): void
    {
        // Public home page should not include admin dropdown assets
        $publicResponse = $this->get('/');
        $publicResponse->assertStatus(200);
        $publicResponse->assertDontSee('assets/css/admin-dropdowns.css');
        $publicResponse->assertDontSee('assets/js/admin-dropdowns.js');

        // Teacher dashboard should not include admin dropdown assets
        $teacherResponse = $this->actingAs($this->teacherUser)->get(route('teacher.dashboard'));
        $teacherResponse->assertStatus(200);
        $teacherResponse->assertDontSee('assets/css/admin-dropdowns.css');
        $teacherResponse->assertDontSee('assets/js/admin-dropdowns.js');
    }

    public function test_admin_teaching_assignments_page_renders_with_selects(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.teaching-assignments.index'));

        $response->assertStatus(200);
        $response->assertSee('name="teacher_id"', false);
        $response->assertSee('name="subject_id"', false);
        $response->assertSee('name="class_id"', false);
    }

    public function test_admin_classes_page_renders_with_selects(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.classes.index'));

        $response->assertStatus(200);
        $response->assertSee('name="homeroom_teacher_id"', false);
        $response->assertSee('name="source_class_id"', false);
    }

    public function test_admin_grades_page_renders_with_filter_selects(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.grades.index'));

        $response->assertStatus(200);
        $response->assertSee('name="class_id"', false);
        $response->assertSee('name="semester_id"', false);
    }
}
