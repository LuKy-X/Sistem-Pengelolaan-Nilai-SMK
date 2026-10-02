<?php

namespace App\Ai\Tools;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\StudentProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Counts students with optional filters by department, grade level, class, or gender.
 */
class GetStudentCount implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Menghitung jumlah siswa aktif. Bisa difilter berdasarkan jurusan (department_name/code), tingkat kelas (grade_level: X/XI/XII), kode/nama kelas, jenis kelamin (gender: L/P), atau status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $departmentSearch = $request['department'] ?? null;
        $gradeLevelCode = $request['grade_level'] ?? null;
        $classSearch = $request['class'] ?? null;
        $gender = $request['gender'] ?? null;
        $groupByDepartment = filter_var($request['group_by_department'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        // If group_by_department requested, return count per department
        if ($groupByDepartment) {
            return $this->countByDepartment($gender);
        }

        $query = StudentProfile::query()
            ->select(['student_profiles.id', 'student_profiles.gender'])
            ->where('student_profiles.status', 'ACTIVE');

        $this->applyFilters($query, $departmentSearch, $gradeLevelCode, $classSearch, $gender);

        $count = $query->count();

        $context = $this->buildContext($departmentSearch, $gradeLevelCode, $classSearch, $gender);

        return json_encode([
            'count' => $count,
            'filter_context' => $context,
        ]);
    }

    /**
     * Count students grouped by department.
     */
    private function countByDepartment(?string $gender): string
    {
        $query = StudentProfile::query()
            ->join('class_enrollments', 'class_enrollments.student_id', '=', 'student_profiles.id')
            ->join('classes', 'classes.id', '=', 'class_enrollments.class_id')
            ->join('departments', 'departments.id', '=', 'classes.department_id')
            ->where('student_profiles.status', 'ACTIVE')
            ->where('class_enrollments.status', 'ACTIVE')
            ->select(['departments.name as department_name', 'departments.short_name', \DB::raw('COUNT(DISTINCT student_profiles.id) as student_count')])
            ->groupBy('departments.id', 'departments.name', 'departments.short_name')
            ->orderByDesc('student_count');

        if ($gender) {
            $normalizedGender = strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false ? 'MALE' : 'FEMALE';
            $query->where('student_profiles.gender', $normalizedGender);
        }

        $results = $query->get();

        return json_encode([
            'by_department' => $results->map(fn ($r) => [
                'department' => $r->department_name.($r->short_name ? " ({$r->short_name})" : ''),
                'count' => (int) $r->student_count,
            ])->values()->all(),
            'total' => $results->sum('student_count'),
        ]);
    }

    /**
     * Apply filters to the student query.
     *
     * @param  Builder<StudentProfile>  $query
     */
    private function applyFilters(
        Builder $query,
        ?string $departmentSearch,
        ?string $gradeLevelCode,
        ?string $classSearch,
        ?string $gender,
    ): void {
        if ($departmentSearch || $gradeLevelCode || $classSearch) {
            $query->join('class_enrollments', function ($join) {
                $join->on('class_enrollments.student_id', '=', 'student_profiles.id')
                    ->where('class_enrollments.status', 'ACTIVE');
            })->join('classes', 'classes.id', '=', 'class_enrollments.class_id');
        }

        if ($departmentSearch) {
            $dept = Department::query()
                ->where('is_active', true)
                ->where(function ($q) use ($departmentSearch) {
                    $q->where('name', 'like', "%{$departmentSearch}%")
                        ->orWhere('short_name', 'like', "%{$departmentSearch}%")
                        ->orWhere('code', 'like', "%{$departmentSearch}%");
                })->first();

            if ($dept) {
                $query->where('classes.department_id', $dept->id);
            }
        }

        if ($gradeLevelCode) {
            $gradeLevel = GradeLevel::query()
                ->where('code', 'like', "%{$gradeLevelCode}%")
                ->orWhere('name', 'like', "%{$gradeLevelCode}%")
                ->first();

            if ($gradeLevel) {
                $query->where('classes.grade_level_id', $gradeLevel->id);
            }
        }

        if ($classSearch) {
            $query->where(function ($q) use ($classSearch) {
                $q->where('classes.name', 'like', "%{$classSearch}%")
                    ->orWhere('classes.code', 'like', "%{$classSearch}%");
            });
        }

        if ($gender) {
            $normalizedGender = strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false ? 'MALE' : 'FEMALE';
            $query->where('student_profiles.gender', $normalizedGender);
        }
    }

    /**
     * Build a human-readable context string for the filters applied.
     */
    private function buildContext(
        ?string $department,
        ?string $gradeLevel,
        ?string $class,
        ?string $gender,
    ): string {
        $parts = ['siswa aktif'];

        if ($department) {
            $parts[] = "jurusan {$department}";
        }

        if ($gradeLevel) {
            $parts[] = "kelas {$gradeLevel}";
        }

        if ($class) {
            $parts[] = "kelas {$class}";
        }

        if ($gender) {
            $genderLabel = strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false
                ? 'laki-laki'
                : 'perempuan';
            $parts[] = "jenis kelamin {$genderLabel}";
        }

        return implode(', ', $parts);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'department' => $schema->string()
                ->description('Nama, singkatan, atau kode jurusan. Contoh: "RPL", "Rekayasa Perangkat Lunak", "TKJT".'),
            'grade_level' => $schema->string()
                ->description('Tingkat kelas. Contoh: "X", "XI", "XII", "10", "11", "12".'),
            'class' => $schema->string()
                ->description('Nama atau kode kelas spesifik. Contoh: "XII RPL C", "RPL A".'),
            'gender' => $schema->string()
                ->description('Jenis kelamin: "L" untuk laki-laki, "P" untuk perempuan.'),
            'group_by_department' => $schema->boolean()
                ->description('Jika true, kembalikan jumlah siswa per jurusan. Default: false.')
                ->default(false),
        ];
    }
}
