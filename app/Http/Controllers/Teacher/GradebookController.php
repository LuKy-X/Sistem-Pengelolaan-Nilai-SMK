<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.gradeLevel',
            'subject',
            'semester.academicYear',
            'gradebooks.columns',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        return view('teacher.gradebooks.index', compact('assignments'));
    }

    public function show(Gradebook $gradebook): View
    {
        Gate::authorize('view', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'categories.columns' => fn ($q) => $q->orderBy('sort_order'),
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student.user',
        ]);

        $columns = $gradebook->columns;
        $categories = $gradebook->categories;
        $students = $gradebook->students->sortBy('student.full_name');

        // Fetch all scores for this gradebook mapped as [student_id][column_id] => score
        $scoresCollection = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get();

        $scoresMatrix = [];
        foreach ($scoresCollection as $score) {
            $scoresMatrix[$score->student_id][$score->gradebook_column_id] = $score->final_score;
        }

        // Summary calculations per column
        $columnAverages = [];
        foreach ($columns as $column) {
            $colScores = $scoresCollection->where('gradebook_column_id', $column->id)->pluck('final_score')->filter(fn ($v) => ! is_null($v));
            $columnAverages[$column->id] = $colScores->isNotEmpty() ? round($colScores->avg(), 1) : '-';
        }

        return view('teacher.gradebooks.show', compact(
            'gradebook',
            'categories',
            'columns',
            'students',
            'scoresMatrix',
            'columnAverages'
        ));
    }

    public function storeColumn(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20'],
            'category_id' => ['nullable', 'exists:gradebook_categories,id'],
            'column_type' => ['required', 'string', 'in:SCORE,SUMMARY,ATTENDANCE_SUMMARY,MANUAL'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $highestSortOrder = (int) $gradebook->columns()->max('sort_order');

        $gradebook->columns()->create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'category_id' => $validated['category_id'] ?? null,
            'column_type' => GradebookColumnType::from($validated['column_type']),
            'calculation_type' => $validated['column_type'] === 'SUMMARY' ? GradebookCalculationType::Average : null,
            'max_score' => $validated['max_score'],
            'weight' => $validated['weight'],
            'sort_order' => $highestSortOrder + 1,
            'is_visible' => true,
            'is_included_in_average' => true,
        ]);

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Kolom nilai baru berhasil ditambahkan.');
    }

    public function updateScores(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $scoresData = $request->input('scores', []);
        $teacher = Auth::user()->teacherProfile;

        DB::transaction(function () use ($scoresData, $gradebook, $teacher) {
            foreach ($scoresData as $studentId => $colValues) {
                foreach ($colValues as $columnId => $scoreVal) {
                    $column = GradebookColumn::where('id', $columnId)
                        ->where('gradebook_id', $gradebook->id)
                        ->first();

                    if (! $column) {
                        continue;
                    }

                    if ($scoreVal === null || $scoreVal === '') {
                        GradebookScore::where('gradebook_column_id', $columnId)
                            ->where('student_id', $studentId)
                            ->delete();

                        continue;
                    }

                    $scoreFloat = min((float) $scoreVal, (float) $column->max_score);
                    $scoreFloat = max(0, $scoreFloat);

                    GradebookScore::updateOrCreate(
                        [
                            'gradebook_column_id' => $columnId,
                            'student_id' => $studentId,
                        ],
                        [
                            'raw_score' => $scoreFloat,
                            'final_score' => $scoreFloat,
                            'max_score_snapshot' => $column->max_score,
                            'graded_by' => $teacher->id,
                            'graded_at' => now(),
                        ]
                    );
                }
            }
        });

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Nilai siswa berhasil disimpan.');
    }

    public function export(Gradebook $gradebook): StreamedResponse
    {
        Gate::authorize('view', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student',
        ]);

        $columns = $gradebook->columns;
        $students = $gradebook->students->sortBy('student.full_name');

        $scores = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get();

        $scoresMatrix = [];
        foreach ($scores as $s) {
            $scoresMatrix[$s->student_id][$s->gradebook_column_id] = $s->final_score;
        }

        $fileName = 'buku_nilai_'.str_replace(' ', '_', strtolower($gradebook->name)).'_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($columns, $students, $scoresMatrix) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            $headers = ['No', 'NIS', 'Nama Siswa'];
            foreach ($columns as $c) {
                $headers[] = $c->name." ({$c->code})";
            }
            fputcsv($handle, $headers);

            // CSV Rows
            $no = 1;
            foreach ($students as $entry) {
                $row = [
                    $no++,
                    $entry->student?->nis ?? '-',
                    $entry->student?->full_name ?? 'Siswa',
                ];
                foreach ($columns as $c) {
                    $row[] = $scoresMatrix[$entry->student_id][$c->id] ?? '';
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
