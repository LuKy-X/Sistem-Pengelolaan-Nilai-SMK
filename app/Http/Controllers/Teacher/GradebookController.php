<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ClassEnrollment;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\GradebookStudent;
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
            'schoolClass.department',
            'subject',
            'semester.academicYear',
            'gradebooks.columns',
            'gradebooks.students',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id');

        $gradebooksQuery = Gradebook::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'));

        if ($selectedAssignmentId) {
            $gradebooksQuery->where('teaching_assignment_id', $selectedAssignmentId);
        }

        $gradebooks = $gradebooksQuery->latest()->get();

        return view('teacher.gradebooks.index', compact('assignments', 'gradebooks', 'selectedAssignmentId'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Gradebook::class);

        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.department',
            'subject',
            'semester.academicYear',
            'schoolClass.enrollments.student',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);

        return view('teacher.gradebooks.create', compact('assignments', 'selectedAssignmentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Gradebook::class);

        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'columns' => ['nullable', 'array'],
            'columns.*.name' => ['required_with:columns', 'string', 'max:100'],
            'columns.*.code' => ['required_with:columns', 'string', 'max:50'],
            'columns.*.column_type' => ['required_with:columns', 'string', 'in:SCORE,SUMMARY'],
            'columns.*.calculation_type' => ['nullable', 'string', 'in:AVERAGE,SUM,WEIGHTED_AVERAGE'],
            'columns.*.max_score' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'columns.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);
        if ($assignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak membuat buku nilai untuk kelas ini.');
        }

        $gradebook = DB::transaction(function () use ($validated, $assignment, $request) {
            $gradebook = Gradebook::create([
                'teaching_assignment_id' => $assignment->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Auto enroll active students in the class
            $enrollments = ClassEnrollment::where('class_id', $assignment->class_id)
                ->where('status', 'ACTIVE')
                ->get();

            foreach ($enrollments as $enrollment) {
                GradebookStudent::firstOrCreate(
                    [
                        'gradebook_id' => $gradebook->id,
                        'student_id' => $enrollment->student_id,
                    ],
                    [
                        'class_enrollment_id' => $enrollment->id,
                        'status' => 'ACTIVE',
                        'joined_at' => now(),
                    ]
                );
            }

            // Save configured columns
            if (! empty($validated['columns'])) {
                foreach ($validated['columns'] as $index => $col) {
                    $isSummary = ($col['column_type'] ?? 'SCORE') === 'SUMMARY';
                    $colType = $isSummary ? GradebookColumnType::Summary : GradebookColumnType::Score;
                    $calcType = null;
                    if ($isSummary && ! empty($col['calculation_type'])) {
                        $calcType = match (strtoupper($col['calculation_type'])) {
                            'SUM' => GradebookCalculationType::Sum,
                            'WEIGHTED_AVERAGE' => GradebookCalculationType::WeightedAverage,
                            default => GradebookCalculationType::Average,
                        };
                    }

                    GradebookColumn::create([
                        'gradebook_id' => $gradebook->id,
                        'name' => $col['name'],
                        'code' => strtoupper($col['code']),
                        'column_type' => $colType,
                        'calculation_type' => $calcType,
                        'max_score' => $col['max_score'] ?? 100.00,
                        'weight' => $col['weight'] ?? 10.00,
                        'sort_order' => $index + 1,
                        'is_visible' => true,
                        'is_included_in_average' => true,
                    ]);
                }
            }

            return $gradebook;
        });

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Buku nilai berhasil dibuat dan siap digunakan.');
    }

    public function edit(Gradebook $gradebook): View
    {
        Gate::authorize('update', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('teacher.gradebooks.edit', compact('gradebook'));
    }

    public function update(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $gradebook->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('teacher.gradebooks.index')->with('success', 'Buku nilai berhasil diperbarui.');
    }

    public function destroy(Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('delete', $gradebook);

        DB::transaction(function () use ($gradebook) {
            $columnIds = $gradebook->columns()->pluck('id');
            Assessment::whereIn('gradebook_column_id', $columnIds)->update(['gradebook_column_id' => null]);
            GradebookScore::whereIn('gradebook_column_id', $columnIds)->delete();
            $gradebook->columns()->delete();
            $gradebook->students()->delete();
            $gradebook->categories()->delete();
            $gradebook->delete();
        });

        return redirect()->route('teacher.gradebooks.index')->with('success', 'Buku nilai berhasil dihapus.');
    }

    public function destroyColumn(Gradebook $gradebook, GradebookColumn $column): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        if ($column->gradebook_id !== $gradebook->id) {
            abort(403, 'Kolom bukan milik buku nilai ini.');
        }

        DB::transaction(function () use ($column) {
            Assessment::where('gradebook_column_id', $column->id)->update(['gradebook_column_id' => null]);
            GradebookScore::where('gradebook_column_id', $column->id)->delete();
            $column->delete();
        });

        return redirect()->back()->with('success', 'Kolom nilai berhasil dihapus.');
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
