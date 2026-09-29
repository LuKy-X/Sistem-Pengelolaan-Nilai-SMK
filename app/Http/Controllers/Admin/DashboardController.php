<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Article;
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
        $recentArticles = Article::latest()->take(3)->get();

        return view('admin.dashboard', compact(
            'activeYear',
            'activeSemester',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalDepartments',
            'attendanceStats',
            'recentClasses',
            'recentArticles'
        ));
    }
}
