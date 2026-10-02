<?php

namespace App\Ai\Tools;

use App\Models\TeachingAssignment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves current teacher and subject assignments grouped by class.
 */
class GetTeachingAssignments implements Tool
{
    private const RESULT_LIMIT = 500;

    public function description(): Stringable|string
    {
        return 'Mengambil data guru pengampu dan mata pelajaran, dikelompokkan per kelas aktif. Bisa difilter berdasarkan nama/kode kelas, jurusan, atau tingkat. Gunakan untuk pertanyaan guru pengampu per kelas.';
    }

    public function handle(Request $request): Stringable|string
    {
        $classSearch = $request['class'] ?? null;
        $departmentSearch = $request['department'] ?? null;
        $gradeLevelSearch = $request['grade_level'] ?? null;

        $query = TeachingAssignment::query()
            ->with([
                'teacher:id,full_name',
                'subject:id,name,code',
                'schoolClass:id,name,code,department_id,grade_level_id',
                'schoolClass.department:id,name,short_name',
                'schoolClass.gradeLevel:id,code,name',
            ])
            ->where('is_active', true)
            ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('status', 'ACTIVE'))
            ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('is_active', true))
            ->whereHas('semester', fn ($semesterQuery) => $semesterQuery->where('is_active', true))
            ->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('is_active', true))
            ->when($classSearch, fn ($assignments) => $assignments->whereHas(
                'schoolClass',
                fn ($classQuery) => $classQuery
                    ->where('name', 'like', "%{$classSearch}%")
                    ->orWhere('code', 'like', "%{$classSearch}%"),
            ))
            ->when($departmentSearch, fn ($assignments) => $assignments->whereHas(
                'schoolClass.department',
                fn ($departmentQuery) => $departmentQuery
                    ->where('name', 'like', "%{$departmentSearch}%")
                    ->orWhere('short_name', 'like', "%{$departmentSearch}%")
                    ->orWhere('code', 'like', "%{$departmentSearch}%"),
            ))
            ->when($gradeLevelSearch, fn ($assignments) => $assignments->whereHas(
                'schoolClass.gradeLevel',
                fn ($gradeQuery) => $gradeQuery
                    ->where('code', 'like', "%{$gradeLevelSearch}%")
                    ->orWhere('name', 'like', "%{$gradeLevelSearch}%"),
            ))
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->orderBy('id');

        $assignments = $query->limit(self::RESULT_LIMIT + 1)->get();
        $truncated = $assignments->count() > self::RESULT_LIMIT;
        $assignments = $assignments->take(self::RESULT_LIMIT);

        $classes = $assignments
            ->filter(fn (TeachingAssignment $assignment): bool => $assignment->schoolClass !== null)
            ->groupBy('class_id')
            ->map(function ($classAssignments): array {
                $schoolClass = $classAssignments->first()->schoolClass;

                return [
                    'class' => $schoolClass->name,
                    'code' => $schoolClass->code,
                    'grade_level' => $schoolClass->gradeLevel?->code,
                    'department' => $schoolClass->department?->short_name ?: $schoolClass->department?->name,
                    'teachers' => $classAssignments
                        ->filter(fn (TeachingAssignment $assignment): bool => $assignment->teacher !== null)
                        ->map(fn (TeachingAssignment $assignment): array => [
                            'subject' => $assignment->subject?->name,
                            'teacher' => $assignment->teacher->full_name,
                            'weekly_hours' => $assignment->weekly_hours,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return json_encode([
            'found' => $classes !== [],
            'classes' => $classes,
            'total_classes' => count($classes),
            'total_assignments' => $assignments->count(),
            'truncated' => $truncated,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'class' => $schema->string()
                ->description('Nama atau kode kelas, misalnya "XII RPL C".'),
            'department' => $schema->string()
                ->description('Nama, singkatan, atau kode jurusan, misalnya "RPL".'),
            'grade_level' => $schema->string()
                ->description('Tingkat kelas: "X", "XI", atau "XII".'),
        ];
    }
}
