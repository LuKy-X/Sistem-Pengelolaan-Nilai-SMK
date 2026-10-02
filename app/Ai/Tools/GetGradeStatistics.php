<?php

namespace App\Ai\Tools;

use App\Models\GradebookScore;
use App\Models\Subject;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Provides grade statistics (average, min, max) for subjects or classes.
 *
 * This tool only returns aggregated statistics — never individual student grades —
 * to protect student privacy. Detailed per-student grades require authentication.
 */
class GetGradeStatistics implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil statistik nilai (rata-rata, tertinggi, terendah) untuk mata pelajaran atau kelas. HANYA menampilkan data agregat, bukan nilai per siswa. Untuk menjawab pertanyaan "berapa rata-rata nilai...", "siapa siswa dengan nilai tertinggi...", dll.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $subjectSearch = $request['subject'] ?? null;
        $classSearch = $request['class'] ?? null;
        $departmentSearch = $request['department'] ?? null;
        $showTopStudents = filter_var($request['show_top_students'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        $query = GradebookScore::query()
            ->whereNotNull('final_score')
            ->join('gradebook_columns', 'gradebook_columns.id', '=', 'gradebook_scores.gradebook_column_id')
            ->join('gradebooks', 'gradebooks.id', '=', 'gradebook_columns.gradebook_id')
            ->join('teaching_assignments', 'teaching_assignments.id', '=', 'gradebooks.teaching_assignment_id')
            ->join('subjects', 'subjects.id', '=', 'teaching_assignments.subject_id')
            ->join('classes', 'classes.id', '=', 'teaching_assignments.class_id');

        if ($subjectSearch) {
            $subject = Subject::query()
                ->where('name', 'like', "%{$subjectSearch}%")
                ->orWhere('code', 'like', "%{$subjectSearch}%")
                ->first();

            if (! $subject) {
                return json_encode(['error' => "Mata pelajaran '{$subjectSearch}' tidak ditemukan."]);
            }

            $query->where('subjects.id', $subject->id);
        }

        if ($classSearch) {
            $query->where(function ($q) use ($classSearch) {
                $q->where('classes.name', 'like', "%{$classSearch}%")
                    ->orWhere('classes.code', 'like', "%{$classSearch}%");
            });
        }

        if ($departmentSearch) {
            $query->join('departments', 'departments.id', '=', 'classes.department_id')
                ->where(function ($q) use ($departmentSearch) {
                    $q->where('departments.name', 'like', "%{$departmentSearch}%")
                        ->orWhere('departments.short_name', 'like', "%{$departmentSearch}%")
                        ->orWhere('departments.code', 'like', "%{$departmentSearch}%");
                });
        }

        $stats = (clone $query)
            ->selectRaw('
                AVG(gradebook_scores.final_score) as avg_score,
                MAX(gradebook_scores.final_score) as max_score,
                MIN(gradebook_scores.final_score) as min_score,
                COUNT(gradebook_scores.id) as total_scores
            ')
            ->first();

        $result = [
            'statistics' => [
                'average' => $stats->avg_score ? round((float) $stats->avg_score, 2) : null,
                'highest' => $stats->max_score ? round((float) $stats->max_score, 2) : null,
                'lowest' => $stats->min_score ? round((float) $stats->min_score, 2) : null,
                'total_scores' => (int) ($stats->total_scores ?? 0),
            ],
            'context' => array_filter([
                'subject' => $subjectSearch,
                'class' => $classSearch,
                'department' => $departmentSearch,
            ]),
        ];

        if ($stats->total_scores === 0 || $stats->total_scores === null) {
            $result['message'] = 'Belum ada data nilai yang tersedia untuk filter tersebut.';

            return json_encode($result);
        }

        // Optionally show top 3 students (names only, no sensitive data)
        if ($showTopStudents && $subjectSearch) {
            $topStudents = (clone $query)
                ->join('student_profiles', 'student_profiles.id', '=', 'gradebook_scores.student_id')
                ->select([
                    'student_profiles.full_name',
                    DB::raw('MAX(gradebook_scores.final_score) as best_score'),
                ])
                ->groupBy('student_profiles.id', 'student_profiles.full_name')
                ->orderByDesc('best_score')
                ->limit(3)
                ->get();

            $result['top_students'] = $topStudents->map(fn ($s) => [
                'name' => $s->full_name,
                'score' => round((float) $s->best_score, 2),
            ])->values()->all();
        }

        return json_encode($result);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'subject' => $schema->string()
                ->description('Nama atau kode mata pelajaran. Contoh: "Matematika", "Bahasa Indonesia".'),
            'class' => $schema->string()
                ->description('Nama atau kode kelas. Contoh: "XII RPL C".'),
            'department' => $schema->string()
                ->description('Nama atau kode jurusan. Contoh: "RPL".'),
            'show_top_students' => $schema->boolean()
                ->description('Tampilkan 3 siswa dengan nilai tertinggi (hanya nama, bukan data sensitif). Default: false.')
                ->default(false),
        ];
    }
}
