<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\LateReductionType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentLatePolicy;
use App\Models\AssessmentSubmission;
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

        $assignments = TeachingAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);

        $assessmentsQuery = Assessment::with([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'gradebookColumn',
            'submissions',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'));

        if ($selectedAssignmentId) {
            $assessmentsQuery->where('teaching_assignment_id', $selectedAssignmentId);
        }

        $assessments = $assessmentsQuery->latest()->get();

        return view('teacher.assessments.index', compact(
            'assignments',
            'assessments',
            'selectedAssignmentId'
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

        return view('teacher.assessments.create', compact(
            'assignments',
            'selectedAssignment',
            'rubrics',
            'previewGradebook',
            'previewStudents',
            'previewColumns',
            'previewScoresMatrix'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Assessment::class);

        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'gradebook_column_id' => ['nullable', 'exists:gradebook_columns,id'],
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
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
                'UH', 'ULANGAN_HARIAN', 'MID', 'UTS', 'SEM', 'UAS' => AssessmentType::Exam,
                'QUIZ', 'KUIS' => AssessmentType::Quiz,
                'PROJEK', 'PROJECT', 'PRAKTIK' => AssessmentType::Project,
                default => AssessmentType::Task,
            };

            $assessment = Assessment::create([
                'teaching_assignment_id' => $assignment->id,
                'gradebook_column_id' => $validated['gradebook_column_id'] ?? null,
                'type' => $typeEnum,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
                'submission_required' => true,
                'rubric_id' => $request->boolean('use_rubric') ? ($validated['rubric_id'] ?? null) : null,
                'created_by' => $teacher->id,
                'published_at' => now(),
                'status' => AssessmentStatus::Published,
            ]);

            // Late penalty policy
            $hasLatePolicy = $request->boolean('enable_late_policy');
            if ($hasLatePolicy) {
                AssessmentLatePolicy::create([
                    'assessment_id' => $assessment->id,
                    'enabled' => true,
                    'reduction_type' => LateReductionType::FixedPoints,
                    'reduction_value' => $validated['reduction_value'] ?? 5.00,
                    'interval' => $validated['interval'] === 'MINGGU' ? 7 : 1,
                    'grace_period_minutes' => 0,
                    'minimum_max_score' => 50.00,
                ]);
            }
        });

        return redirect()->route('teacher.assessments.index', [
            'assignment_id' => $validated['teaching_assignment_id'],
        ])->with('success', 'Tugas / asesmen berhasil dibuat dan dipublikasikan ke siswa.');
    }

    public function show(Assessment $assessment): View
    {
        Gate::authorize('view', $assessment);

        $assessment->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'gradebookColumn',
            'rubric.criteria.levels',
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
