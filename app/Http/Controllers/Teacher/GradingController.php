<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use App\Models\RubricScore;
use App\Models\TeachingAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradingController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.department',
            'schoolClass.enrollments.student.user',
            'subject',
            'semester.academicYear',
            'schedules',
            'gradebooks.columns' => fn ($q) => $q->orderBy('sort_order'),
            'gradebooks.columns.category',
            'gradebooks.columns.assessments.rubric.criteria',
            'gradebooks.columns.assessments.submissions.media',
            'gradebooks.columns.assessments.submissions.student.user',
            'gradebooks.columns.scores.rubricScores',
            'gradebooks.students.student.user',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $assessments = Assessment::with([
            'gradebookColumn',
            'rubric.criteria',
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'submissions.media',
            'submissions.student.user',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'))
            ->latest()
            ->get();

        $rubrics = Rubric::where('created_by', $teacher->id)
            ->with('criteria')
            ->latest()
            ->get();

        return view('teacher.grading.index', compact('assignments', 'assessments', 'rubrics'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $teacher = Auth::user()->teacherProfile;

        if (! $request->has('score') && $request->has('final_score')) {
            $request->merge(['score' => $request->input('final_score')]);
        }

        if (! $request->has('feedback') && $request->has('teacher_notes')) {
            $request->merge(['feedback' => $request->input('teacher_notes')]);
        }

        $validated = $request->validate([
            'gradebook_column_id' => ['required', 'exists:gradebook_columns,id'],
            'student_id' => ['required', 'exists:student_profiles,id'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:500'],
            'rubric_scores' => ['nullable', 'array'],
            'rubric_items' => ['nullable', 'array'],
        ]);

        $column = GradebookColumn::findOrFail($validated['gradebook_column_id']);
        $gradebook = $column->gradebook;

        if ($gradebook->teachingAssignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak memberi nilai pada buku nilai ini.');
        }

        $scoreRecord = DB::transaction(function () use ($validated, $column, $teacher, $request) {
            $scoreFloat = min((float) $validated['score'], (float) $column->max_score);
            $scoreFloat = max(0, $scoreFloat);

            $rubricScoresInput = $request->input('rubric_scores') ?? $request->input('rubric_items');
            $hasRubric = ! empty($rubricScoresInput) && is_array($rubricScoresInput);

            $score = GradebookScore::updateOrCreate(
                [
                    'gradebook_column_id' => $column->id,
                    'student_id' => $validated['student_id'],
                ],
                [
                    'raw_score' => $scoreFloat,
                    'final_score' => $scoreFloat,
                    'max_score_snapshot' => $column->max_score,
                    'feedback' => $validated['feedback'] ?? null,
                    'source' => $hasRubric ? 'RUBRIC' : 'MANUAL',
                    'graded_by' => $teacher->id,
                    'graded_at' => now(),
                ]
            );

            if ($hasRubric) {
                foreach ($rubricScoresInput as $key => $pts) {
                    if (! is_numeric($pts)) {
                        continue;
                    }

                    $critId = null;
                    if (is_numeric($key)) {
                        $critId = (int) $key;
                    } else {
                        $crit = RubricCriterion::where('criterion', $key)->first();
                        $critId = $crit?->id;
                    }

                    if ($critId) {
                        RubricScore::updateOrCreate(
                            [
                                'gradebook_score_id' => $score->id,
                                'rubric_criterion_id' => $critId,
                            ],
                            [
                                'points_awarded' => (float) $pts,
                                'graded_by' => $teacher->id,
                            ]
                        );
                    }
                }
            }

            return $score;
        });

        $targetAssignmentId = $gradebook->teaching_assignment_id;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai tugas siswa berhasil disimpan.',
                'score' => $scoreRecord,
            ]);
        }

        return redirect()->route('teacher.grading.index', [
            'assignment_id' => $targetAssignmentId,
            'gradebook_id' => $gradebook->id,
            'column_id' => $column->id,
            'student_id' => $validated['student_id'],
        ])
            ->with('success', 'Nilai tugas siswa berhasil disimpan ke buku nilai.')
            ->with('selected_assignment_id', $targetAssignmentId)
            ->with('selected_gradebook_id', $gradebook->id)
            ->with('selected_column_id', $column->id)
            ->with('selected_student_id', $validated['student_id']);
    }
}
