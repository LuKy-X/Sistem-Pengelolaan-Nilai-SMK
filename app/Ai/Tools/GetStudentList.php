<?php

namespace App\Ai\Tools;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\StudentProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves paginated list of students with optional filters.
 *
 * Limits results to 20 per page to avoid sending too much data to the AI.
 */
class GetStudentList implements Tool
{
    private const MAX_PER_PAGE = 20;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil daftar siswa dengan filter opsional berdasarkan jurusan, tingkat kelas, atau kelas tertentu. Hasilnya dipaginasi (max 20 per halaman). Gunakan untuk menjawab pertanyaan "siapa saja siswa..." atau "daftar siswa...".';
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
        $page = max(1, (int) ($request['page'] ?? 1));

        $query = StudentProfile::query()
            ->select([
                'student_profiles.id',
                'student_profiles.full_name',
                'student_profiles.nis',
                'student_profiles.gender',
                'student_profiles.status',
            ])
            ->where('student_profiles.status', 'ACTIVE')
            ->orderBy('student_profiles.full_name');

        $needsClassJoin = $departmentSearch || $gradeLevelCode || $classSearch;

        if ($needsClassJoin) {
            $query->join('class_enrollments', function ($join) {
                $join->on('class_enrollments.student_id', '=', 'student_profiles.id')
                    ->where('class_enrollments.status', 'ACTIVE');
            })->join('classes', 'classes.id', '=', 'class_enrollments.class_id')
                ->addSelect(['classes.name as class_name', 'classes.code as class_code']);
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
                ->where(function ($q) use ($gradeLevelCode) {
                    $q->where('code', 'like', "%{$gradeLevelCode}%")
                        ->orWhere('name', 'like', "%{$gradeLevelCode}%");
                })->first();

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

        $total = $query->count();
        $students = $query->forPage($page, self::MAX_PER_PAGE)->get();

        return json_encode([
            'students' => $students->map(fn ($s) => [
                'full_name' => $s->full_name,
                'nis' => $s->nis,
                'gender' => $s->gender === 'MALE' ? 'Laki-laki' : 'Perempuan',
                'class' => isset($s->class_name) ? $s->class_name : null,
            ])->values()->all(),
            'total' => $total,
            'page' => $page,
            'per_page' => self::MAX_PER_PAGE,
            'total_pages' => (int) ceil($total / self::MAX_PER_PAGE),
            'has_more' => $total > $page * self::MAX_PER_PAGE,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'department' => $schema->string()
                ->description('Nama, singkatan, atau kode jurusan. Contoh: "RPL", "TKJT".'),
            'grade_level' => $schema->string()
                ->description('Tingkat kelas: "X", "XI", atau "XII".'),
            'class' => $schema->string()
                ->description('Nama atau kode kelas. Contoh: "XII RPL C".'),
            'gender' => $schema->string()
                ->description('Jenis kelamin: "L" untuk laki-laki, "P" untuk perempuan.'),
            'page' => $schema->integer()
                ->description('Halaman data (default: 1).')
                ->default(1),
        ];
    }
}
