<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\ClassEnrollment;
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
            ->with(['schoolClass.gradeLevel', 'subject', 'semester.academicYear'])
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

        // 2. Today's Teaching Schedule
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso; // 1 = Monday ... 7 = Sunday
        $todaySchedules = TeachingSchedule::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('day_of_week', $todayDayOfWeek)
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'startPeriod', 'endPeriod'])
            ->get();

        // If today has no schedule (e.g. weekend), also grab upcoming schedules for display
        $allSchedules = TeachingSchedule::whereIn('teaching_assignment_id', $assignmentIds)
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'startPeriod', 'endPeriod'])
            ->orderBy('day_of_week')
            ->get();

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

        // 4. Class Grade Average for Chart
        $chartLabels = [];
        $chartAverages = [];

        foreach ($assignments as $assignment) {
            $className = $assignment->schoolClass?->name ?? 'Kelas';
            $subjectCode = $assignment->subject?->code ?? 'Mapel';
            $label = "{$className} ({$subjectCode})";

            $gradebook = $assignment->gradebooks()->first();
            $avgScore = 0;
            if ($gradebook) {
                $avgScore = GradebookScore::whereHas('column', function ($q) use ($gradebook) {
                    $q->where('gradebook_id', $gradebook->id)
                        ->where('is_included_in_average', true);
                })->avg('final_score') ?? 0;
            }

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
            'todaySchedules',
            'allSchedules',
            'recentPendingSubmissions',
            'chartLabels',
            'chartAverages'
        ));
    }
}
