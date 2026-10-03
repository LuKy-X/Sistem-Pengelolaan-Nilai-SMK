<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\GradebookScore;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile;

        // If no teacher profile exists yet, create one or graceful fallback
        if (! $teacher) {
            $teacher = $user->teacherProfile()->create([
                'nip' => 'GURU-'.$user->id,
                'full_name' => $user->name,
                'gender' => 'MALE',
                'status' => 'TETAP',
            ]);
        }

        // Active teaching assignments
        $assignments = $teacher->teachingAssignments()
            ->with(['schoolClass.gradeLevel', 'schoolClass.department', 'subject', 'semester.academicYear'])
            ->where('is_active', true)
            ->get();

        $assignmentIds = $assignments->pluck('id');
        $classIds = $assignments->pluck('class_id')->unique();

        // 1. KPI Counts
        $totalClasses = $classIds->count();
        $totalStudents = ClassEnrollment::whereIn('class_id', $classIds)
            ->where('status', 'ACTIVE')
            ->distinct('student_id')
            ->count('student_id');

        $totalAssessments = Assessment::whereIn('teaching_assignment_id', $assignmentIds)->count();

        $pendingReviews = AssessmentSubmission::whereHas('assessment', function ($q) use ($assignmentIds) {
            $q->whereIn('teaching_assignment_id', $assignmentIds);
        })->where('status', 'SUBMITTED')->count();

        $totalJournals = ClassJournal::whereIn('teaching_assignment_id', $assignmentIds)->count();

        // 2. Today's Teaching Schedule (STRICT: only today's schedule)
        $today = Carbon::now(config('app.timezone', 'Asia/Jakarta'));
        $todayDayOfWeek = (int) $today->dayOfWeekIso; // 1 = Monday ... 7 = Sunday

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $todayDayName = $dayNames[$todayDayOfWeek] ?? 'Hari Ini';
        $todayFormatted = $todayDayName.', '.$today->day.' '.($monthNames[$today->month] ?? '').' '.$today->year;

        $todaySchedules = TeachingSchedule::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('day_of_week', $todayDayOfWeek)
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'startPeriod', 'endPeriod'])
            ->orderBy('start_period_id')
            ->get();

        // Check if journal for today's assignments has already been filled
        $todayJournalAssignmentIds = ClassJournal::whereIn('teaching_assignment_id', $assignmentIds)
            ->whereDate('journal_date', $today->toDateString())
            ->pluck('teaching_assignment_id')
            ->all();

        // Full weekly schedules grouped by day for the weekly timetable tab
        $allSchedules = TeachingSchedule::whereIn('teaching_assignment_id', $assignmentIds)
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'startPeriod', 'endPeriod'])
            ->orderBy('day_of_week')
            ->orderBy('start_period_id')
            ->get();

        $weeklySchedulesGrouped = $allSchedules->groupBy('day_of_week');

        // 3. Pending Submissions to Grade
        $recentPendingSubmissions = AssessmentSubmission::whereHas('assessment', function ($q) use ($assignmentIds) {
            $q->whereIn('teaching_assignment_id', $assignmentIds);
        })
            ->where('status', 'SUBMITTED')
            ->with([
                'assessment.teachingAssignment.schoolClass',
                'assessment.teachingAssignment.subject',
                'assessment.gradebookColumn',
                'student',
            ])
            ->latest('submitted_at')
            ->take(5)
            ->get();

        // 4. Recent Class Journals & Attendance (with attendances and class enrollments eager loaded to avoid N+1)
        $recentJournals = ClassJournal::whereIn('teaching_assignment_id', $assignmentIds)
            ->with(['teachingAssignment.schoolClass.enrollments', 'teachingAssignment.subject', 'startPeriod', 'endPeriod', 'attendances'])
            ->latest('journal_date')
            ->latest('id')
            ->take(4)
            ->get();

        // 5. Recent Active Assessments (with submission counts)
        $recentAssessments = Assessment::whereIn('teaching_assignment_id', $assignmentIds)
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject'])
            ->withCount([
                'submissions as total_submissions_count',
                'submissions as pending_submissions_count' => function ($q) {
                    $q->where('status', 'SUBMITTED');
                },
            ])
            ->latest('id')
            ->take(4)
            ->get();

        // 6. Class Grade Average for Chart
        $averagesByAssignment = GradebookScore::query()
            ->join('gradebook_columns', 'gradebook_scores.gradebook_column_id', '=', 'gradebook_columns.id')
            ->join('gradebooks', 'gradebook_columns.gradebook_id', '=', 'gradebooks.id')
            ->whereIn('gradebooks.teaching_assignment_id', $assignmentIds)
            ->where('gradebook_columns.is_included_in_average', true)
            ->selectRaw('gradebooks.teaching_assignment_id, AVG(gradebook_scores.final_score) as avg_score')
            ->groupBy('gradebooks.teaching_assignment_id')
            ->pluck('avg_score', 'gradebooks.teaching_assignment_id');

        $chartLabels = [];
        $chartAverages = [];

        foreach ($assignments as $assignment) {
            $className = $assignment->schoolClass?->name ?? 'Kelas';
            $subjectCode = $assignment->subject?->code ?? 'Mapel';
            $label = "{$className} ({$subjectCode})";

            $avgScore = $averagesByAssignment->get($assignment->id, 0);

            $chartLabels[] = $label;
            $chartAverages[] = round((float) $avgScore, 1);
        }

        // Fallback default chart data if assignments have no scores yet
        if (empty($chartLabels)) {
            $chartLabels = ['XII RPL 1 (MTK)', 'XII RPL 2 (MTK)', 'XII RPL 1 (PWPB)', 'XI RPL 1 (BD)'];
            $chartAverages = [85.5, 79.2, 88.0, 82.4];
        }

        return view('teacher.dashboard.index', compact(
            'teacher',
            'assignments',
            'totalClasses',
            'totalStudents',
            'totalAssessments',
            'pendingReviews',
            'totalJournals',
            'todayDayName',
            'todayFormatted',
            'todaySchedules',
            'todayJournalAssignmentIds',
            'allSchedules',
            'weeklySchedulesGrouped',
            'dayNames',
            'recentPendingSubmissions',
            'recentJournals',
            'recentAssessments',
            'chartLabels',
            'chartAverages'
        ));
    }
}
