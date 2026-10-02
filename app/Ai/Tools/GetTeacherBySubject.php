<?php

namespace App\Ai\Tools;

use App\Models\TeachingAssignment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Finds teachers who teach a specific subject, optionally filtered by class.
 */
class GetTeacherBySubject implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mencari guru yang mengajar mata pelajaran tertentu. Bisa difilter juga berdasarkan kelas atau jurusan tertentu. Contoh: guru matematika, guru yang mengajar di kelas XII RPL.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $subjectSearch = $request['subject'] ?? null;
        $classSearch = $request['class'] ?? null;
        $departmentSearch = $request['department'] ?? null;

        if (! $subjectSearch && ! $classSearch && ! $departmentSearch) {
            return json_encode(['error' => 'Masukkan minimal satu filter: subject, class, atau department.']);
        }

        $query = TeachingAssignment::query()
            ->with(['teacher', 'subject', 'schoolClass.department', 'schoolClass.gradeLevel'])
            ->where('is_active', true);

        if ($subjectSearch) {
            $query->whereHas('subject', function ($q) use ($subjectSearch) {
                $q->where('name', 'like', "%{$subjectSearch}%")
                    ->orWhere('code', 'like', "%{$subjectSearch}%");
            });
        }

        if ($classSearch) {
            $query->whereHas('schoolClass', function ($q) use ($classSearch) {
                $q->where('name', 'like', "%{$classSearch}%")
                    ->orWhere('code', 'like', "%{$classSearch}%");
            });
        }

        if ($departmentSearch) {
            $query->whereHas('schoolClass.department', function ($q) use ($departmentSearch) {
                $q->where('name', 'like', "%{$departmentSearch}%")
                    ->orWhere('short_name', 'like', "%{$departmentSearch}%")
                    ->orWhere('code', 'like', "%{$departmentSearch}%");
            });
        }

        $assignments = $query->limit(30)->get();

        if ($assignments->isEmpty()) {
            $searchDesc = [];
            if ($subjectSearch) {
                $searchDesc[] = "mata pelajaran '{$subjectSearch}'";
            }
            if ($classSearch) {
                $searchDesc[] = "kelas '{$classSearch}'";
            }
            if ($departmentSearch) {
                $searchDesc[] = "jurusan '{$departmentSearch}'";
            }

            return json_encode([
                'teachers' => [],
                'message' => 'Tidak ditemukan penugasan mengajar untuk '.implode(' dan ', $searchDesc).'. Pastikan penugasan mengajar sudah diinput di sistem.',
            ]);
        }

        // Deduplicate: same teacher teaching same subject in multiple classes
        $teacherSubjectMap = [];
        foreach ($assignments as $assignment) {
            if (! $assignment->teacher) {
                continue;
            }

            $key = $assignment->teacher_id.'-'.$assignment->subject_id;

            if (! isset($teacherSubjectMap[$key])) {
                $teacherSubjectMap[$key] = [
                    'teacher_name' => $assignment->teacher->full_name,
                    'subject_name' => $assignment->subject?->name,
                    'classes' => [],
                ];
            }

            if ($assignment->schoolClass) {
                $teacherSubjectMap[$key]['classes'][] = $assignment->schoolClass->name;
            }
        }

        return json_encode([
            'teachers' => array_values($teacherSubjectMap),
            'total' => count($teacherSubjectMap),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'subject' => $schema->string()
                ->description('Nama atau kode mata pelajaran. Contoh: "Matematika", "Bahasa Indonesia", "Pemrograman".'),
            'class' => $schema->string()
                ->description('Nama atau kode kelas. Contoh: "XII RPL C".'),
            'department' => $schema->string()
                ->description('Nama atau kode jurusan. Contoh: "RPL", "TKJT".'),
        ];
    }
}
