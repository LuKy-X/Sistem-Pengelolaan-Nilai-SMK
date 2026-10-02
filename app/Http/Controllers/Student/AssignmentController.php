<?php

namespace App\Http\Controllers\Student;

use App\Enums\AssessmentStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\GradebookScore;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    use RecordsAuditTrail;

    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $classIds = $student->classEnrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        $subjectFilter = $request->query('subject');
        $tab = $request->query('tab', 'aktif');

        $baseQuery = fn () => Assessment::query()
            ->where('status', AssessmentStatus::Published->value)
            ->where('submission_required', true)
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->when($subjectFilter, fn ($query) => $query->whereHas(
                'teachingAssignment',
                fn ($inner) => $inner->where('subject_id', $subjectFilter)
            ));

        // Status tugas siswa diekspresikan sebagai kondisi SQL, bukan dihitung di
        // PHP, supaya penomoran halaman dan jumlah per tab tetap akurat. Kalau
        // dihitung di PHP, seluruh tugas kelas harus dimuat sekaligus.
        $isSubmitted = fn ($query) => $query->whereHas('submissions', fn ($sub) => $sub
            ->where('student_id', $student->id)
            ->whereIn('status', [SubmissionStatus::Submitted->value, SubmissionStatus::Reviewed->value]));

        $isUnsubmitted = fn ($query) => $query->where(function (Builder $inner) use ($student) {
            $inner->whereDoesntHave('submissions', fn ($sub) => $sub->where('student_id', $student->id))
                ->orWhereHas('submissions', fn ($sub) => $sub
                    ->where('student_id', $student->id)
                    ->where('status', SubmissionStatus::Draft->value));
        });

        $applyState = function (Builder $query, string $state) use ($isSubmitted, $isUnsubmitted) {
            return match ($state) {
                'terlewat' => $isUnsubmitted($query)
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now()),
                'selesai' => $isSubmitted($query),
                'semua' => $query,
                default => $isUnsubmitted($query)->where(fn (Builder $inner) => $inner
                    ->whereNull('due_at')
                    ->orWhere('due_at', '>=', now())),
            };
        };

        $counts = [
            'aktif' => $applyState($baseQuery(), 'aktif')->count(),
            'terlewat' => $applyState($baseQuery(), 'terlewat')->count(),
            'selesai' => $applyState($baseQuery(), 'selesai')->count(),
        ];

        $filtered = $applyState($baseQuery(), $tab)
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.schoolClass',
                'teachingAssignment.teacher',
                'latePolicy',
                'submissions' => fn ($query) => $query->where('student_id', $student->id),
            ])
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->paginate(12)
            ->withQueryString();

        // Daftar mapel untuk filter, hanya mapel yang dipakai kelas siswa.
        $subjects = Subject::query()
            ->whereHas('teachingAssignments', fn ($query) => $query->whereIn('class_id', $classIds))
            ->orderBy('name')
            ->get();

        return view('student.assignments.index', [
            'student' => $student,
            'filtered' => $filtered,
            'subjects' => $subjects,
            'subjectFilter' => $subjectFilter,
            'tab' => $tab,
            'counts' => $counts,
        ]);
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

            $this->audit('ASSIGNMENT_SUBMITTED', $submission, [
                'assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'status' => $submission->status,
                'late_minutes' => $submission->late_minutes,
                'has_attachment' => $request->hasFile('attachment'),
            ]);
        });

        $message = $lateMinutes > 0
            ? "Tugas berhasil dikumpulkan, tercatat terlambat {$lateMinutes} menit dari tenggat."
            : 'Tugas berhasil dikumpulkan tepat waktu.';

        return redirect()
            ->route('student.assignments.show', $assessment)
            ->with('success', $message);
    }
}
