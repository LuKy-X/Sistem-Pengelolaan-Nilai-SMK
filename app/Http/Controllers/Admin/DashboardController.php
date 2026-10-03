<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Article;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\JournalAttendance;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $totalStudents = StudentProfile::count();
        $totalTeachers = TeacherProfile::count();
        $totalClasses = SchoolClass::count();
        $totalDepartments = Department::count();

        // Kehadiran hari ini
        $todayAttendances = JournalAttendance::whereHas('journal', function ($query) {
            $query->whereDate('journal_date', today());
        })->get();

        $attendanceStats = [
            'hadir' => $todayAttendances->where('status', AttendanceStatus::Present)->count(),
            'sakit' => $todayAttendances->where('status', AttendanceStatus::Sick)->count(),
            'izin' => $todayAttendances->where('status', AttendanceStatus::Permit)->count(),
            'alpha' => $todayAttendances->where('status', AttendanceStatus::Absent)->count(),
        ];

        // Rombel aktif terbaru
        $recentClasses = SchoolClass::with(['department', 'gradeLevel', 'homeroomTeacher'])
            ->latest()
            ->take(5)
            ->get();

        // Berita sekolah terbaru
        $recentArticles = Article::with('category')->latest()->take(3)->get();

        // Statistik per jurusan untuk chart
        $departments = Department::where('is_active', true)->get();
        $departmentIds = $departments->pluck('id');

        $enrollmentCounts = ClassEnrollment::query()
            ->join('classes', 'class_enrollments.class_id', '=', 'classes.id')
            ->where('class_enrollments.status', 'ACTIVE')
            ->whereIn('classes.department_id', $departmentIds)
            ->selectRaw('classes.department_id, COUNT(DISTINCT class_enrollments.student_id) as total')
            ->groupBy('classes.department_id')
            ->pluck('total', 'classes.department_id');

        $chartLabels = [];
        $chartData = [];
        foreach ($departments as $dept) {
            $chartLabels[] = $dept->short_name ?: $dept->code;
            $chartData[] = (int) ($enrollmentCounts->get($dept->id) ?? 0);
        }

        return view('admin.dashboard', compact(
            'activeYear',
            'activeSemester',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalDepartments',
            'attendanceStats',
            'recentClasses',
            'recentArticles',
            'chartLabels',
            'chartData'
        ));
    }
}
