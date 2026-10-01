<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcademicPaginationAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);
    }

    public function test_subjects_list_is_paginated_and_can_be_searched_and_filtered(): void
    {
        $dept1 = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $dept2 = Department::create([
            'code' => 'TKJ',
            'name' => 'Teknik Komputer & Jaringan',
            'is_active' => true,
        ]);

        // Buat 15 mata pelajaran
        for ($i = 1; $i <= 15; $i++) {
            Subject::create([
                'code' => sprintf('MP-%02d', $i),
                'name' => $i === 1 ? 'Matematika Terapan' : ($i === 2 ? 'Pemrograman Berorientasi Objek' : "Mata Pelajaran {$i}"),
                'category' => $i % 2 === 0 ? 'MUATAN_KEJURUAN' : 'MUATAN_NASIONAL',
                'department_id' => $i === 2 ? $dept1->id : ($i === 3 ? $dept2->id : null),
                'is_active' => true,
            ]);
        }

        // 1. Verifikasi paginasi default halaman 1 (hanya 10 data dari 15)
        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.subjects.index'));
        $response->assertStatus(200);
        $response->assertSee('Menampilkan', false);
        $response->assertSee('dari total', false);
        $response->assertDontSee('dark:bg-gray', false);
        $response->assertViewHas('subjects', function ($subjects) {
            return $subjects->count() === 10 && $subjects->total() === 15 && $subjects->currentPage() === 1;
        });

        // 2. Verifikasi halaman 2 memiliki sisa 5 data
        $responsePage2 = $this->actingAs($this->adminUser)->get(route('admin.academic.subjects.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('subjects', function ($subjects) {
            return $subjects->count() === 5 && $subjects->currentPage() === 2;
        });

        // 3. Verifikasi pencarian keyword nama mapel
        $responseSearch = $this->actingAs($this->adminUser)->get(route('admin.academic.subjects.index', [
            'search' => 'Matematika Terapan',
        ]));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Matematika Terapan');
        $responseSearch->assertDontSee('Pemrograman Berorientasi Objek');

        // 4. Verifikasi filter berdasarkan jurusan khusus
        $responseDept = $this->actingAs($this->adminUser)->get(route('admin.academic.subjects.index', [
            'department_id' => $dept1->id,
        ]));
        $responseDept->assertStatus(200);
        $responseDept->assertSee('Pemrograman Berorientasi Objek');
        $responseDept->assertDontSee('Matematika Terapan');

        // 5. Verifikasi filter berdasarkan kategori mapel
        $responseCat = $this->actingAs($this->adminUser)->get(route('admin.academic.subjects.index', [
            'category' => 'MUATAN_KEJURUAN',
        ]));
        $responseCat->assertStatus(200);
        $responseCat->assertViewHas('subjects', function ($subjects) {
            return $subjects->where('category', 'MUATAN_NASIONAL')->isEmpty();
        });
    }

    public function test_classes_list_is_paginated(): void
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $dept = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $gradeLevel = GradeLevel::create([
            'code' => 'X',
            'name' => 'Kelas X',
            'level' => 10,
        ]);

        for ($i = 1; $i <= 15; $i++) {
            SchoolClass::create([
                'academic_year_id' => $year->id,
                'department_id' => $dept->id,
                'grade_level_id' => $gradeLevel->id,
                'name' => "X RPL {$i}",
                'code' => "X-RPL-{$i}",
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.classes.index'));
        $response->assertStatus(200);
        $response->assertViewHas('classes', function ($classes) {
            return $classes->count() === 12 && $classes->total() === 15;
        });
    }

    public function test_teaching_assignments_list_is_paginated(): void
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $year->id,
            'name' => 'Semester Gasal',
            'semester_number' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $dept = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $gradeLevel = GradeLevel::create([
            'code' => 'X',
            'name' => 'Kelas X',
            'level' => 10,
        ]);

        $subject = Subject::create([
            'code' => 'RPL-01',
            'name' => 'Dasar Pemrograman',
            'category' => 'MUATAN_KEJURUAN',
            'is_active' => true,
        ]);

        $teacherUser = User::factory()->create();
        $teacher = TeacherProfile::create([
            'user_id' => $teacherUser->id,
            'nip' => '198501012010011001',
            'full_name' => 'Budi Santoso, S.Kom.',
            'status' => 'ACTIVE',
        ]);

        for ($i = 1; $i <= 18; $i++) {
            $class = SchoolClass::create([
                'academic_year_id' => $year->id,
                'department_id' => $dept->id,
                'grade_level_id' => $gradeLevel->id,
                'name' => "Rombel {$i}",
                'code' => "RBL-{$i}",
                'is_active' => true,
            ]);

            TeachingAssignment::create([
                'semester_id' => $semester->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'weekly_hours' => 2,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.teaching-assignments.index'));
        $response->assertStatus(200);
        $response->assertViewHas('assignments', function ($assignments) {
            return $assignments->count() === 15 && $assignments->total() === 18;
        });
    }

    public function test_departments_list_is_paginated(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Department::create([
                'code' => "JUR-{$i}",
                'name' => "Jurusan Kejuruan {$i}",
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.departments.index'));
        $response->assertStatus(200);
        $response->assertViewHas('departments', function ($departments) {
            return $departments->count() === 9 && $departments->total() === 12;
        });
    }

    public function test_academic_years_list_is_paginated(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            AcademicYear::create([
                'name' => '202'.$i.'/202'.($i + 1),
                'start_date' => '202'.$i.'-07-01',
                'end_date' => '202'.($i + 1).'-06-30',
                'is_active' => $i === 1,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.years.index'));
        $response->assertStatus(200);
        $response->assertViewHas('academicYears', function ($academicYears) {
            return $academicYears->count() === 5 && $academicYears->total() === 7;
        });
    }

    public function test_semesters_list_is_paginated(): void
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 14; $i++) {
            Semester::create([
                'academic_year_id' => $year->id,
                'name' => "Semester {$i}",
                'semester_number' => $i % 2 === 1 ? 1 : 2,
                'start_date' => '2026-07-01',
                'end_date' => '2026-12-31',
                'is_active' => $i === 1,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.academic.semesters.index'));
        $response->assertStatus(200);
        $response->assertViewHas('semesters', function ($semesters) {
            return $semesters->count() === 10 && $semesters->total() === 14;
        });
    }

    public function test_ppdb_periods_list_is_paginated(): void
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 14; $i++) {
            AdmissionPeriod::create([
                'academic_year_id' => $year->id,
                'title' => "Gelombang Pendaftaran {$i}",
                'registration_start' => '2026-05-01',
                'registration_end' => '2026-06-01',
                'status' => 'OPEN',
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.cms.ppdb'));
        $response->assertStatus(200);
        $response->assertViewHas('periods', function ($periods) {
            return $periods->count() === 10 && $periods->total() === 14;
        });
    }
}
