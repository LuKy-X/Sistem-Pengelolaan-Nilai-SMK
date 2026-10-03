<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeachingSchedule;
use Illuminate\Http\Request;
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

    /**
     * Nama hari ringkas untuk strip ringkasan, supaya ketujuh hari tetap muat
     * satu baris pada layar laptop dan tidak memakan tinggi halaman.
     *
     * @var array<int, string>
     */
    public const DAY_SHORT_NAMES = [
        1 => 'Sen',
        2 => 'Sel',
        3 => 'Rab',
        4 => 'Kam',
        5 => 'Jum',
        6 => 'Sab',
        7 => 'Min',
    ];

    /**
     * Halaman hanya menampilkan satu hari pada satu waktu, dipilih lewat query
     * string ?day= supaya pilihan bertahan saat halaman di-refresh dan URL-nya
     * bisa dibagikan.
     *
     * Nilai di luar 1-7 diabaikan dan jatuh ke hari saat siswa membuka halaman.
     * Bukan error: tautan lama atau inputaksi tidak sengaja tidak boleh
     * membuat halaman gagal dibuka.
     */
    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $enrollment = $student->currentEnrollment()->with('schoolClass.department')->first();

        $selectedDay = $request->integer('day');

        if ($selectedDay < 1 || $selectedDay > 7) {
            $selectedDay = now()->dayOfWeekIso;
        }

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

        $daySchedules = $schedules->get($selectedDay, collect());

        // Jumlah pelajaran per hari untuk ketujuh hari, termasuk hari kosong.
        // Ini yang membuat siswa bisa melihat hari mana yang terpadat tanpa
        // harus membuka setiap hari satu per satu.
        $dayCounts = collect(self::DAY_NAMES)
            ->mapWithKeys(fn (string $name, int $day) => [$day => $schedules->get($day, collect())->count()]);

        return view('student.schedules.index', [
            'student' => $student,
            'enrollment' => $enrollment,
            'daySchedules' => $daySchedules,
            'dayCounts' => $dayCounts,
            'selectedDay' => $selectedDay,
            'today' => now()->dayOfWeekIso,
            'previousDay' => $selectedDay === 1 ? 7 : $selectedDay - 1,
            'nextDay' => $selectedDay === 7 ? 1 : $selectedDay + 1,
        ]);
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
