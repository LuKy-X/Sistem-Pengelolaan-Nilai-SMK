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

    /**
     * Detail satu slot jadwal. Siswa hanya boleh melihat slot yang teachernya
     * mengajar di kelas yang sedang ia ikuti.
     */
    public function show(TeachingSchedule $schedule): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $classIds = $student->classEnrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        abort_if($classIds->isEmpty(), 403, 'Anda belum terdaftar pada kelas manapun.');

        // Endpoint ini menerima id slot dari URL, jadi kepemilikan wajib diperiksa
        // ulang: tanpa ini siswa bisa membuka jadwal kelas orang lain hanya dengan
        // menebak id.
        $belongs = TeachingSchedule::query()
            ->whereKey($schedule->getKey())
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->exists();

        abort_unless($belongs, 403, 'Jadwal ini bukan dari kelas Anda.');

        $schedule->load([
            'startPeriod',
            'endPeriod',
            'teachingAssignment.subject',
            'teachingAssignment.teacher.user',
            'teachingAssignment.schoolClass',
            'teachingAssignment.semester.academicYear',
        ]);

        $journal = $schedule->journals()->latest('journal_date')->first();

        return view('student.schedules.show', [
            'student' => $student,
            'schedule' => $schedule,
            'journal' => $journal,
            'dayName' => self::DAY_NAMES[$schedule->day_of_week] ?? 'Hari tidak ditentukan',
        ]);
    }
}
