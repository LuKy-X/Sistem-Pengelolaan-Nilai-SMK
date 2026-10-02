<?php

namespace App\Http\Controllers\Student;

use App\Enums\AssessmentStatus;
use App\Enums\ExitPermitStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\GradebookScore;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use HandlesDisciplinePoints;

    public function index(): View
    {
        $user = Auth::user();
        $student = $user->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $student->loadMissing(['currentEnrollment.schoolClass.department', 'currentEnrollment.schoolClass.gradeLevel']);

        $classIds = $student->classEnrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        // Jadwal pelajaran hari ini dari teaching assignment aktif di kelas siswa.
        $todaySchedules = TeachingSchedule::query()
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->where('day_of_week', Carbon::now()->dayOfWeekIso)
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher.user', 'startPeriod', 'endPeriod'])
            ->get()
            ->sortBy(fn ($schedule) => $schedule->startPeriod?->period_number ?? 99)
            ->values();

        // Izin keluar yang sedang aktif (disetujui BK, belum kembali).
        $activePermit = $student->exitPermits()
            ->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
            ->whereNull('actual_return_at')
            ->with('reason')
            ->latest('approved_at')
            ->first();

        $pendingPermit = $student->exitPermits()
            ->where('status', ExitPermitStatus::Pending->value)
            ->with('reason')
            ->latest('requested_at')
            ->first();

        // Tugas yang diterbitkan guru untuk kelas siswa.
        $publishedAssessments = Assessment::query()
            ->where('status', AssessmentStatus::Published->value)
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->with([
                'teachingAssignment.subject',
                'submissions' => fn ($query) => $query->where('student_id', $student->id),
            ])
            ->latest('published_at')
            ->get();

        $latestAssignments = $publishedAssessments->take(5);

        $unfinishedAssignments = $publishedAssessments
            ->filter(function (Assessment $assessment) {
                if (! $assessment->submission_required) {
                    return false;
                }

                $submission = $assessment->submissions->first();

                return $submission === null || $submission->status === SubmissionStatus::Draft;
            })
            ->sortBy(fn (Assessment $assessment) => $assessment->due_at ?? now()->addYears(10))
            ->take(5)
            ->values();

        $unfinishedCount = $publishedAssessments
            ->filter(function (Assessment $assessment) {
                if (! $assessment->submission_required) {
                    return false;
                }

                $submission = $assessment->submissions->first();

                return $submission === null || $submission->status === SubmissionStatus::Draft;
            })
            ->count();

        // Nilai terbaru yang diberikan guru lewat buku nilai.
        $recentGrades = GradebookScore::query()
            ->where('student_id', $student->id)
            ->whereHas('column', fn ($query) => $query->where('is_visible', true))
            ->with(['column.gradebook.teachingAssignment.subject'])
            ->latest('graded_at')
            ->take(5)
            ->get();

        // Ringkasan kedisiplinan, memakai kalkulasi yang sama dengan modul BK.
        $academicYear = $this->activeAcademicYear();
        $setting = $this->disciplineSetting($academicYear?->id);
        $balance = $this->pointBalance($student->id, $setting, $academicYear?->id);
        $standing = $this->disciplineStanding($balance, $setting);

        return view('student.dashboard', compact(
            'student',
            'todaySchedules',
            'activePermit',
            'pendingPermit',
            'latestAssignments',
            'unfinishedAssignments',
            'unfinishedCount',
            'recentGrades',
            'balance',
            'standing',
        ));
    }
}
