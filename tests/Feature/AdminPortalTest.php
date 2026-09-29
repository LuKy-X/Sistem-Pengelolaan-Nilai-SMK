<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getAdminUser(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('code', 'ADMIN'))->first();
    }

    private function getTeacherUser(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('code', 'TEACHER'))->first();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $teacher = $this->getTeacherUser();

        $response = $this->actingAs($teacher)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_academic_years_index(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.academic.years.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Tahun Ajaran');
    }

    public function test_admin_can_store_academic_year(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.academic.years.store'), [
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('admin.academic.years.index'));
        $this->assertDatabaseHas('academic_years', [
            'name' => '2027/2028',
        ]);
    }

    public function test_admin_can_view_departments(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.academic.departments.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Jurusan');
    }

    public function test_admin_can_store_department(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.academic.departments.store'), [
            'code' => 'TKJ',
            'name' => 'Teknik Komputer dan Jaringan',
            'short_name' => 'TKJ',
            'description' => 'Jurusan teknik jaringan komputer dan telekomunikasi',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.departments.index'));
        $this->assertDatabaseHas('departments', [
            'code' => 'TKJ',
            'name' => 'Teknik Komputer dan Jaringan',
        ]);
    }

    public function test_admin_can_store_school_class(): void
    {
        $admin = $this->getAdminUser();
        $year = AcademicYear::first();
        $dept = Department::first();
        $grade = GradeLevel::first();

        $response = $this->actingAs($admin)->post(route('admin.academic.classes.store'), [
            'academic_year_id' => $year->id,
            'department_id' => $dept->id,
            'grade_level_id' => $grade->id,
            'code' => 'X-TEST-1',
            'name' => 'X Test 1',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.classes.index'));
        $this->assertDatabaseHas('classes', [
            'code' => 'X-TEST-1',
            'name' => 'X Test 1',
        ]);
    }

    public function test_admin_can_store_subject(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.academic.subjects.store'), [
            'code' => 'TEST-01',
            'name' => 'Kewirausahaan dan Bisnis Digital',
            'category' => 'MUATAN_KEJURUAN',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'code' => 'TEST-01',
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }
}
