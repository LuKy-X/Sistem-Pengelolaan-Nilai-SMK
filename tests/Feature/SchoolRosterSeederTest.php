<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentProfile;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\GradeLevelSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SampleClassSeeder;
use Database\Seeders\SemesterSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolRosterSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_class_seeder_imports_all_workbook_rosters_idempotently(): void
    {
        $this->seed([
            RoleSeeder::class,
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            GradeLevelSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            SampleClassSeeder::class,
        ]);

        $academicYear = AcademicYear::query()->where('is_active', true)->firstOrFail();
        $rosterStudents = StudentProfile::query()->where('nis', 'like', 'SEED-%');
        $genderByNis = $rosterStudents->orderBy('nis')->pluck('gender', 'nis')->all();
        $adilsClass = StudentProfile::query()
            ->where('full_name', 'ADIL MIFTAHUL HUDA')
            ->firstOrFail()
            ->currentEnrollment
            ->schoolClass;
        $tkrClass = SchoolClass::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('code', 'XI-TKR-1')
            ->firstOrFail();

        $this->assertSame('2026/2027', $academicYear->name);
        $this->assertSame('2026-07-01', $academicYear->start_date->toDateString());
        $this->assertSame(36, SchoolClass::query()->where('academic_year_id', $academicYear->id)->count());
        $this->assertSame(1277, StudentProfile::query()->where('nis', 'like', 'SEED-%')->count());
        $this->assertSame(
            1277,
            ClassEnrollment::query()
                ->whereHas('student', fn ($query) => $query->where('nis', 'like', 'SEED-%'))
                ->where('status', 'ACTIVE')
                ->count()
        );
        $this->assertSame(1277, $rosterStudents->distinct('nisn')->count('nisn'));
        $this->assertSame(0, $rosterStudents->whereNotIn('gender', ['MALE', 'FEMALE'])->count());
        $this->assertSame('XII-RPL-3', $adilsClass->code);
        $this->assertSame(36, $tkrClass->students()->where('nis', 'like', 'SEED-%')->count());

        $this->assertSame('Mesin', Department::query()->where('code', 'TPM')->value('name'));
        $this->assertSame('Tekstil', Department::query()->where('code', 'TPK')->value('name'));
        $this->assertSame('2026-07-01', Semester::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_number', 1)
            ->value('start_date')
            ->toDateString());

        $this->seed(SampleClassSeeder::class);

        $this->assertSame(1277, StudentProfile::query()->where('nis', 'like', 'SEED-%')->count());
        $this->assertSame($genderByNis, StudentProfile::query()->where('nis', 'like', 'SEED-%')->orderBy('nis')->pluck('gender', 'nis')->all());
        $this->assertSame(36, SchoolClass::query()->where('academic_year_id', $academicYear->id)->count());
    }
}
