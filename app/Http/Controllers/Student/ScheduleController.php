<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Nama hari Indonesia berdasarkan day_of_week ISO (1 = Senin ... 7 = Minggu).
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function index(): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $enrollment = $student->currentEnrollment()->with('schoolClass.department')->first();

        $schedules = collect();

        if ($enrollment !== null) {
            $schedules = TeachingSchedule::query()
                ->whereHas('teachingAssignment', function ($query) use ($enrollment) {
                    $query->where('class_id', $enrollment->class_id)->where('is_active', true);
                })
                ->with([
                    'teachingAssignment.subject',
                    'teachingAssignment.teacher',
                    'teachingAssignment.semester.academicYear',
                    'startPeriod',
                    'endPeriod',
                ])
                ->get()
                ->sortBy([
                    ['day_of_week', 'asc'],
                    fn ($a, $b) => ($a->startPeriod?->period_number ?? 99) <=> ($b->startPeriod?->period_number ?? 99),
                ])
                ->groupBy('day_of_week');
        }

        $dayNames = self::DAY_NAMES;

        return view('student.schedules.index', compact('student', 'enrollment', 'schedules', 'dayNames'));
    }
}
