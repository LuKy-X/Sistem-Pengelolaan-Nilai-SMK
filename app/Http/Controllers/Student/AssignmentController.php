<?php

namespace App\Http\Controllers\Student;

use App\Enums\AssessmentStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\GradebookScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $classIds = $student->classEnrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        $subjectFilter = $request->query('subject');
        $tab = $request->query('tab', 'aktif');

        $assessments = Assessment::query()
            ->where('status', AssessmentStatus::Published->value)
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->when($subjectFilter, function ($query) use ($subjectFilter) {
                $query->whereHas('teachingAssignment', fn ($inner) => $inner->where('subject_id', $subjectFilter));
            })
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.schoolClass',
                'teachingAssignment.teacher',
                'latePolicy',
                'submissions' => fn ($query) => $query->where('student_id', $student->id),
            ])
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->get();

        // Daftar mapel untuk filter, hanya mapel yang benar-benar punya tugas terbit.
        $subjects = $assessments
            ->map(fn (Assessment $assessment) => $assessment->teachingAssignment?->subject)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $categorized = $assessments->map(function (Assessment $assessment) {
            $submission = $assessment->submissions->first();
            $isSubmitted = $submission !== null && in_array($submission->status, [SubmissionStatus::Submitted, SubmissionStatus::Reviewed], true);
            $isOverdue = $assessment->due_at !== null && $assessment->due_at->isPast();

            $assessment->student_submission = $submission;
            $assessment->student_state = match (true) {
                $isSubmitted => 'selesai',
                $isOverdue => 'terlewat',
                default => 'aktif',
            };

            return $assessment;
        });

        $counts = [
            'aktif' => $categorized->where('student_state', 'aktif')->count(),
            'terlewat' => $categorized->where('student_state', 'terlewat')->count(),
            'selesai' => $categorized->where('student_state', 'selesai')->count(),
        ];

        // "semua" menampilkan seluruh riwayat tugas, bukan hanya satu status.
        $filtered = match ($tab) {
            'terlewat', 'selesai' => $categorized->where('student_state', $tab)->values(),
            'semua' => $categorized->values(),
            default => $categorized->where('student_state', 'aktif')->values(),
        };

        return view('student.assignments.index', compact(
            'student',
            'filtered',
            'subjects',
            'subjectFilter',
            'tab',
            'counts',
        ));
    }

    public function show(Assessment $assessment): View
    {
        Gate::authorize('view', $assessment);

        // Siswa hanya melihat tugas yang sudah diterbitkan guru.
        abort_unless($assessment->status === AssessmentStatus::Published, 404);

        $student = Auth::user()->studentProfile;

        $assessment->load([
            'teachingAssignment.subject',
            'teachingAssignment.schoolClass',
            'teachingAssignment.teacher',
            'gradebookColumn',
            'latePolicy',
            'rubric.criteria',
        ]);

        $submission = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->with('media')
            ->first();

        // Nilai akhir pada kolom buku nilai yang tertaut, termasuk rincian rubrik.
        $score = null;
        if ($assessment->gradebook_column_id) {
            $score = GradebookScore::query()
                ->where('gradebook_column_id', $assessment->gradebook_column_id)
                ->where('student_id', $student->id)
                ->with('rubricScores.criterion')
                ->first();
        }

        return view('student.assignments.show', compact('student', 'assessment', 'submission', 'score'));
    }

    public function submit(SubmitAssignmentRequest $request, Assessment $assessment): RedirectResponse
    {
        $student = $request->user()->studentProfile;

        if ($assessment->status !== AssessmentStatus::Published) {
            return back()->with('error', 'Tugas ini belum diterbitkan atau sudah diarsipkan.');
        }

        if (! $assessment->submission_required) {
            return back()->with('error', 'Tugas ini tidak memerlukan pengumpulan daring.');
        }

        $existing = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing !== null && $existing->status === SubmissionStatus::Reviewed) {
            return back()->with('error', 'Pengumpulan Anda sudah dinilai guru dan tidak dapat diubah lagi.');
        }

        $validated = $request->validated();

        // Keterlambatan dihitung deterministik dari deadline, bukan input klien.
        $lateMinutes = 0;
        if ($assessment->due_at !== null && $assessment->due_at->isPast()) {
            $lateMinutes = (int) round($assessment->due_at->diffInMinutes(now(), false));
        }

        DB::transaction(function () use ($request, $validated, $assessment, $student, $lateMinutes) {
            // Jawaban teks hanya ditimpa bila siswa benar-benar mengirim teks baru.
            // Kalau siswa mengirim ulang hanya dengan lampiran, jawaban lama harus
            // dipertahankan — bukan di-reset menjadi NULL.
            $submissionData = [
                'submitted_at' => now(),
                'status' => SubmissionStatus::Submitted,
                'late_minutes' => $lateMinutes,
            ];

            if ($request->filled('content')) {
                $submissionData['content'] = $validated['content'];
            }

            $submission = AssessmentSubmission::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'student_id' => $student->id,
                ],
                $submissionData
            );

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store("submissions/{$assessment->id}/{$student->id}", 'public');

                $submission->media()->create([
                    'collection' => 'submission_attachment',
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        });

        $message = $lateMinutes > 0
            ? "Tugas berhasil dikumpulkan, tercatat terlambat {$lateMinutes} menit dari tenggat."
            : 'Tugas berhasil dikumpulkan tepat waktu.';

        return redirect()
            ->route('student.assignments.show', $assessment)
            ->with('success', $message);
    }
}
