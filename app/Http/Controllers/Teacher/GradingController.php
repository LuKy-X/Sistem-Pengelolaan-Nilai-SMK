<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\TeachingAssignment;
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
            'gradebooks.students.student.user',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $assessments = Assessment::with([
            'gradebookColumn',
            'rubric.criteria.levels',
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'))
            ->latest()
            ->get();

        return view('teacher.grading.index', compact('assignments', 'assessments'));
    }

    public function store(Request $request): RedirectResponse
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
        ]);

        $column = GradebookColumn::findOrFail($validated['gradebook_column_id']);
        $gradebook = $column->gradebook;

        if ($gradebook->teachingAssignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak memberi nilai pada buku nilai ini.');
        }

        DB::transaction(function () use ($validated, $column, $teacher) {
            $scoreFloat = min((float) $validated['score'], (float) $column->max_score);
            $scoreFloat = max(0, $scoreFloat);

            GradebookScore::updateOrCreate(
                [
                    'gradebook_column_id' => $column->id,
                    'student_id' => $validated['student_id'],
                ],
                [
                    'raw_score' => $scoreFloat,
                    'final_score' => $scoreFloat,
                    'max_score_snapshot' => $column->max_score,
                    'feedback' => $validated['feedback'] ?? null,
                    'graded_by' => $teacher->id,
                    'graded_at' => now(),
                ]
            );
        });

        return redirect()->back()->with('success', 'Nilai tugas siswa berhasil disimpan ke buku nilai.');
    }
}
