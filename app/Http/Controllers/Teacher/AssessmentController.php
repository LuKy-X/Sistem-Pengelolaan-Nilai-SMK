<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\LateReductionType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentLatePolicy;
use App\Models\AssessmentSubmission;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\Rubric;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssessmentController extends Controller
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
            'gradebooks.columns' => fn ($q) => $q->orderBy('sort_order')->with('category', 'assessments.latePolicy', 'assessments.rubric.criteria'),
            'gradebooks.students.student.user',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);

        $assessmentsQuery = Assessment::with([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'gradebookColumn',
            'submissions',
            'latePolicy',
            'rubric.criteria',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'));

        if ($selectedAssignmentId) {
            $assessmentsQuery->where('teaching_assignment_id', $selectedAssignmentId);
        }

        $assessments = $assessmentsQuery->latest()->get();
        $rubrics = Rubric::with(['criteria' => fn ($q) => $q->orderBy('sort_order')])
            ->where('created_by', $teacher->id)
            ->latest()
            ->get();

        return view('teacher.assessments.index', compact(
            'assignments',
            'assessments',
            'selectedAssignmentId',
            'rubrics'
        ));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Assessment::class);

        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass',
            'subject',
            'gradebooks.columns',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);
        $selectedAssignment = $assignments->firstWhere('id', $selectedAssignmentId) ?? $assignments->first();

        $rubrics = Rubric::where('created_by', $teacher->id)->latest()->get();

        // Get preview gradebook data for the selected assignment to display the mockup spreadsheet table
        $previewGradebook = $selectedAssignment?->gradebooks()?->first();
        $previewStudents = [];
        $previewColumns = [];
        $previewScoresMatrix = [];

        if ($previewGradebook) {
            $previewGradebook->load(['columns' => fn ($q) => $q->orderBy('sort_order'), 'students.student']);
            $previewColumns = $previewGradebook->columns;
            $previewStudents = $previewGradebook->students->take(5);

            $scores = GradebookScore::whereIn('gradebook_column_id', $previewColumns->pluck('id'))
                ->whereIn('student_id', $previewStudents->pluck('student_id'))
                ->get();

            foreach ($scores as $s) {
                $previewScoresMatrix[$s->student_id][$s->gradebook_column_id] = $s->final_score;
            }
        }

        $gradebookColumns = $previewColumns;

        return view('teacher.assessments.create', compact(
            'assignments',
            'selectedAssignment',
            'rubrics',
            'previewGradebook',
            'previewStudents',
            'previewColumns',
            'previewScoresMatrix',
            'gradebookColumns'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Assessment::class);

        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'assessment_id' => ['nullable', 'exists:assessments,id'],
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'gradebook_column_id' => ['nullable', 'exists:gradebook_columns,id'],
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'status' => ['nullable', 'string', 'in:PUBLISHED,DRAFT,ARCHIVED'],
            'submission_required' => ['nullable', 'boolean'],
            'enable_late_policy' => ['nullable', 'boolean'],
            'reduction_value' => ['nullable', 'numeric', 'min:0'],
            'interval' => ['nullable', 'string'],
            'use_default_policy' => ['nullable', 'boolean'],
            'use_rubric' => ['nullable', 'boolean'],
            'rubric_id' => ['nullable', 'exists:rubrics,id'],
        ]);

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);
        if ($assignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak membuat tugas untuk kelas ini.');
        }

        DB::transaction(function () use ($validated, $assignment, $teacher, $request) {
            // Map or convert type to AssessmentType enum
            $typeEnum = match (strtoupper($validated['type'])) {
                'QUIZ', 'KUIS' => AssessmentType::Quiz,
                'PROJECT', 'PROJEK', 'PRAKTIK' => AssessmentType::Project,
                'EXAM', 'ULANGAN', 'UH', 'MID', 'SEM', 'UAS', 'UTS', 'ULANGAN_HARIAN' => AssessmentType::Exam,
                'REMEDIAL', 'REMIDI' => AssessmentType::Remedial,
                'OTHER', 'LAINNYA' => AssessmentType::Other,
                default => AssessmentType::Task,
            };

            $statusStr = strtoupper($request->input('status', 'DRAFT'));
            $statusEnum = match ($statusStr) {
                'PUBLISHED' => AssessmentStatus::Published,
                'ARCHIVED' => AssessmentStatus::Archived,
                default => AssessmentStatus::Draft,
            };

            $isPublished = ($statusEnum === AssessmentStatus::Published);
            $submissionRequired = $request->boolean('submission_required', false);

            $assessmentData = [
                'teaching_assignment_id' => $assignment->id,
                'gradebook_column_id' => $validated['gradebook_column_id'] ?? null,
                'type' => $typeEnum,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $submissionRequired ? ($validated['instructions'] ?? null) : null,
                'due_at' => $validated['due_at'] ?? null,
                'submission_required' => $submissionRequired,
                'rubric_id' => $request->boolean('use_rubric') ? ($validated['rubric_id'] ?? null) : null,
                'created_by' => $teacher->id,
                'status' => $statusEnum,
            ];

            if (! empty($validated['assessment_id'])) {
                $assessment = Assessment::where('id', $validated['assessment_id'])
                    ->where('teaching_assignment_id', $assignment->id)
                    ->firstOrFail();

                if ($isPublished && ! $assessment->published_at) {
                    $assessmentData['published_at'] = now();
                } elseif (! $isPublished) {
                    $assessmentData['published_at'] = null;
                } else {
                    $assessmentData['published_at'] = $assessment->published_at;
                }

                $assessment->update($assessmentData);
            } else {
                $assessmentData['published_at'] = $isPublished ? now() : null;
                $assessment = Assessment::create($assessmentData);
            }

            if (! empty($validated['gradebook_column_id']) && isset($validated['max_score'])) {
                $column = GradebookColumn::find($validated['gradebook_column_id']);
                if ($column) {
                    $column->update(['max_score' => $validated['max_score']]);
                }
            }

            // Late penalty policy (Default nonaktif)
            $hasLatePolicy = $request->boolean('enable_late_policy', false);

            if ($hasLatePolicy) {
                $isDefault = $request->boolean('use_default_policy');
                $reductionValue = $isDefault ? 5.00 : (float) ($validated['reduction_value'] ?? 5.00);
                $interval = $isDefault ? 7 : (strtoupper($request->input('interval') ?? 'MINGGU') === 'HARI' ? 1 : 7);

                $policyData = [
                    'enabled' => true,
                    'reduction_type' => LateReductionType::FixedPoints,
                    'reduction_value' => $reductionValue,
                    'interval' => $interval,
                    'grace_period_minutes' => 0,
                    'minimum_max_score' => 50.00,
                ];

                if ($assessment->latePolicy) {
                    $assessment->latePolicy->update($policyData);
                } else {
                    AssessmentLatePolicy::create(array_merge(['assessment_id' => $assessment->id], $policyData));
                }
            } else {
                if ($assessment->latePolicy) {
                    $assessment->latePolicy->update(['enabled' => false]);
                } else {
                    AssessmentLatePolicy::create([
                        'assessment_id' => $assessment->id,
                        'enabled' => false,
                        'reduction_type' => LateReductionType::FixedPoints,
                        'reduction_value' => 5.00,
                        'interval' => 7,
                        'grace_period_minutes' => 0,
                        'minimum_max_score' => 50.00,
                    ]);
                }
            }
        });

        $targetColumnId = $validated['gradebook_column_id'] ?? null;
        $targetGradebookId = null;
        if ($targetColumnId) {
            $column = GradebookColumn::find($targetColumnId);
            $targetGradebookId = $column?->gradebook_id;
        }

        return redirect()->route('teacher.assessments.index', [
            'assignment_id' => $validated['teaching_assignment_id'],
        ])
            ->with('success', 'Tugas / asesmen berhasil disimpan.')
            ->with('selected_gradebook_id', $targetGradebookId)
            ->with('selected_column_id', $targetColumnId);
    }

    public function show(Assessment $assessment): View
    {
        Gate::authorize('view', $assessment);

        $assessment->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'gradebookColumn',
            'rubric.criteria',
            'latePolicy',
            'submissions.student.user',
        ]);

        return view('teacher.assessments.show', compact('assessment'));
    }

    public function gradeSubmission(Request $request, Assessment $assessment, AssessmentSubmission $submission): RedirectResponse
    {
        Gate::authorize('update', $assessment);

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $score = (float) $validated['score'];
        $teacher = Auth::user()->teacherProfile;

        // Apply late deduction if applicable
        $lateDeduction = 0;
        if ($submission->is_late && $assessment->latePolicy && $assessment->latePolicy->enabled) {
            $lateDeduction = (float) $assessment->latePolicy->reduction_value;
            $score = max((float) $assessment->latePolicy->minimum_max_score, $score - $lateDeduction);
        }

        DB::transaction(function () use ($submission, $score, $lateDeduction, $validated, $teacher, $assessment) {
            $submission->update([
                'score' => $score,
                'feedback' => $validated['feedback'] ?? null,
                'graded_by' => $teacher->id,
                'graded_at' => now(),
                'status' => 'GRADED',
            ]);

            // Sync score into gradebook if column is linked
            if ($assessment->gradebook_column_id) {
                GradebookScore::updateOrCreate(
                    [
                        'gradebook_column_id' => $assessment->gradebook_column_id,
                        'student_id' => $submission->student_id,
                    ],
                    [
                        'raw_score' => (float) $validated['score'],
                        'final_score' => $score,
                        'late_deduction' => $lateDeduction,
                        'feedback' => $validated['feedback'] ?? null,
                        'graded_by' => $teacher->id,
                        'graded_at' => now(),
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Nilai pengumpulan berhasil disimpan.');
    }
}
