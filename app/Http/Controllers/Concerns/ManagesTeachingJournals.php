<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\AttendanceStatus;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Logika bersama halaman "Absensi Kelas" untuk Guru pengajar dan Guru BK.
 *
 * Guru pengajar dan Guru BK memakai alur yang sama: daftar kelas berasal dari
 * penugasan mengajar milik profil guru itu sendiri, jurnal ditampilkan per hari
 * dengan navigasi mingguan, dan pengisian absensi mengikuti jadwal mengajar.
 */
trait ManagesTeachingJournals
{
    /**
     * Status absensi yang boleh dikirim formulir, sudah dinormalisasi huruf besar.
     *
     * @var list<string>
     */
    protected static array $journalAllowedAbsenceStatuses = [
        'SAKIT', 'IZIN', 'ALPHA',
        'S', 'I', 'A',
        'SICK', 'PERMIT', 'PERMITTED', 'ABSENT',
    ];

    /**
     * Profil guru pengajar dari pengguna yang sedang masuk. Akun Guru BK yang belum
     * memiliki profil akan dibuatkan terlebih dahulu agar penugasan mengajar dan
     * jadwal absensi dapat digunakan.
     */
    protected function journalTeacherProfile(): ?TeacherProfile
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        if (! $user->teacherProfile && $user->isCounselor()) {
            TeacherProfile::ensureCounselorProfiles();
            $user->refresh();
        }

        return $user->teacherProfile;
    }

    /**
     * Data halaman "Absensi Kelas" untuk satu profil guru pengajar/BK.
     *
     * @param  array<string, mixed>  $options  Kunci: routes, requireScheduleMatch, showExportMenu, heading, subheading, emptyMessage
     * @return array<string, mixed>
     */
    protected function teachingJournalWorkspace(Request $request, ?TeacherProfile $teacher, array $options = []): array
    {
        abort_if($teacher === null, 403, 'Profil guru tidak ditemukan. Hubungi administrator.');

        $routes = $options['routes'] ?? [];
        $requireScheduleMatch = (bool) ($options['requireScheduleMatch'] ?? false);

        $assignments = TeachingAssignment::with([
            'subject',
            'semester.academicYear',
            'schedules.startPeriod',
            'schedules.endPeriod',
            'schoolClass' => fn (Relation $classRelation) => $classRelation
                ->with(['gradeLevel', 'department'])
                ->withCount([
                    'enrollments as active_enrollments_count' => fn (Builder $enrollmentQuery) => $enrollmentQuery
                        ->where('status', 'ACTIVE'),
                ]),
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id');
        $selectedAssignment = $selectedAssignmentId ? $assignments->firstWhere('id', $selectedAssignmentId) : null;
        $selectedDate = $this->journalDateQuery($request->query('date'));

        $referenceDate = Carbon::parse($selectedDate);
        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);
        $todayStr = now()->format('Y-m-d');
        $isCurrentWeek = now()->betweenIncluded($weekStart, $weekEnd);

        // Jurnal milik guru ini pada minggu terpilih dimuat dalam satu query.
        $weekJournals = ClassJournal::whereIn('teaching_assignment_id', $assignments->pluck('id'))
            ->where('created_by', $teacher->id)
            ->whereBetween('journal_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get()
            ->groupBy('teaching_assignment_id');

        $this->attachJournalScheduleSummaries($assignments, $weekStart, $weekJournals, $todayStr, $isCurrentWeek);

        $journals = collect();
        $enrolledStudents = collect();
        $allDayPeriods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();
        $lessonPeriods = LessonPeriod::where('is_break', false)->orderBy('period_number')->get();
        $occupiedPeriods = [];
        $previousJournalAttendances = collect();
        $previousJournalInfo = null;
        $defaultStartPeriodId = null;
        $defaultEndPeriodId = null;
        $matchedSchedule = null;

        if ($selectedAssignment) {
            // Jurnal ditampilkan hanya untuk hari terpilih, urut dari jam pelajaran pertama.
            $journals = ClassJournal::withActiveClassStudentCount()
                ->with([
                    'startPeriod',
                    'endPeriod',
                    'attendances.student',
                    'creator.user',
                    'teachingAssignment.subject',
                ])
                ->whereHas('teachingAssignment', function ($query) use ($selectedAssignment) {
                    $query->where('class_id', $selectedAssignment->class_id);
                })
                ->whereDate('journal_date', $selectedDate)
                ->get()
                ->sortBy(fn (ClassJournal $journal) => $journal->startPeriod?->period_number ?? 0)
                ->values();

            foreach ($journals as $journal) {
                $startNumber = $journal->startPeriod?->period_number;
                $endNumber = $journal->endPeriod?->period_number ?? $startNumber;

                if ($startNumber === null || $endNumber === null) {
                    continue;
                }

                for ($period = min($startNumber, $endNumber); $period <= max($startNumber, $endNumber); $period++) {
                    $occupiedPeriods[$period] = [
                        'journal_id' => $journal->id,
                        'period_number' => $period,
                        'subject' => $journal->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                        'teacher' => $journal->creator?->user?->name ?? 'Guru',
                    ];
                }
            }

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedAssignment->class_id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name')
                ->values();

            // Jurnal terakhir pada kelas ini untuk menyalin rekap kehadiran.
            $latestJournal = $journals->last() ?? ClassJournal::withActiveClassStudentCount()
                ->with([
                    'startPeriod',
                    'endPeriod',
                    'attendances.student',
                    'creator.user',
                    'teachingAssignment.subject',
                ])
                ->whereHas('teachingAssignment', function ($query) use ($selectedAssignment) {
                    $query->where('class_id', $selectedAssignment->class_id);
                })
                ->whereDate('journal_date', '<=', $selectedDate)
                ->orderByDesc('journal_date')
                ->orderByDesc('start_period_id')
                ->first();

            if ($latestJournal) {
                $previousJournalInfo = [
                    'date' => $latestJournal->journal_date?->format('d/m/Y'),
                    'start_period' => $latestJournal->startPeriod?->period_number,
                    'end_period' => $latestJournal->endPeriod?->period_number,
                    'subject' => $latestJournal->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                    'teacher' => $latestJournal->creator?->user?->name ?? $latestJournal->creator?->full_name ?? 'Guru',
                    'hadir_count' => $latestJournal->hadir_count,
                    'sakit_count' => $latestJournal->sakit_count,
                    'izin_count' => $latestJournal->izin_count,
                    'alpha_count' => $latestJournal->alpha_count,
                ];

                $previousJournalAttendances = $latestJournal->attendances
                    ->filter(fn (JournalAttendance $attendance) => $attendance->status !== AttendanceStatus::Present)
                    ->map(fn (JournalAttendance $attendance) => [
                        'student_id' => $attendance->student_id,
                        'student_name' => $attendance->student?->full_name ?? ('Siswa #'.$attendance->student_id),
                        'status' => $this->journalAbsenceLabel($attendance->status),
                        'note' => $attendance->note ?? '',
                    ])
                    ->values();
            }

            // Jam default mengikuti jadwal hari tersebut bila masih kosong, jika tidak pakai jam kosong pertama.
            $dayOfWeekNumber = Carbon::parse($selectedDate)->dayOfWeekIso;
            $matchedSchedule = $selectedAssignment->schedules->firstWhere('day_of_week', $dayOfWeekNumber);

            [$defaultStartPeriodId, $defaultEndPeriodId] = $this->resolveJournalDefaultPeriods(
                $lessonPeriods,
                $occupiedPeriods,
                $matchedSchedule
            );
        }

        $hasFilledJournalOnDate = $journals->where('created_by', $teacher->id)->isNotEmpty();

        return [
            'assignments' => $assignments,
            'selectedAssignment' => $selectedAssignment,
            'selectedDate' => $selectedDate,
            'journals' => $journals,
            'enrolledStudents' => $enrolledStudents,
            'allDayPeriods' => $allDayPeriods,
            'lessonPeriods' => $lessonPeriods,
            'occupiedPeriods' => $occupiedPeriods,
            'previousJournalAttendances' => $previousJournalAttendances,
            'previousJournalInfo' => $previousJournalInfo,
            'defaultStartPeriodId' => $defaultStartPeriodId,
            'defaultEndPeriodId' => $defaultEndPeriodId,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'isCurrentWeek' => $isCurrentWeek,
            'prevWeekDate' => $weekStart->copy()->subWeek()->format('Y-m-d'),
            'nextWeekDate' => $weekStart->copy()->addWeek()->format('Y-m-d'),
            'currentWeekDate' => $todayStr,
            'matchedSchedule' => $matchedSchedule,
            'hasFilledJournalOnDate' => $hasFilledJournalOnDate,
            'journalIndexRoute' => $routes['index'] ?? null,
            'journalStoreRoute' => $routes['store'] ?? null,
            'journalUpdateRoute' => $routes['update'] ?? null,
            'journalDestroyRoute' => $routes['destroy'] ?? null,
            'journalShowExportMenu' => (bool) ($options['showExportMenu'] ?? false),
            'journalHeading' => $options['heading'] ?? 'Absensi Kelas',
            'journalSubheading' => $options['subheading'] ?? 'Kelola Absensi Kelas & Jurnal Harian Mengajar',
            'journalEmptyMessage' => $options['emptyMessage'] ?? 'Belum ada kelas yang terdaftar pada penugasan mengajar Anda.',
            'attendanceGate' => $this->journalAttendanceGate($selectedDate, $matchedSchedule, $requireScheduleMatch),
            'lateNotice' => $this->journalLateNotice($selectedDate, $hasFilledJournalOnDate),
        ];
    }

    /**
     * Ringkasan status jadwal per penugasan mengajar untuk ditampilkan di kartu kelas.
     *
     * @param  Collection<int, TeachingAssignment>  $assignments
     * @param  Collection<int, Collection<int, ClassJournal>>  $weekJournals
     */
    protected function attachJournalScheduleSummaries(
        Collection $assignments,
        Carbon $weekStart,
        Collection $weekJournals,
        string $todayStr,
        bool $isCurrentWeek
    ): void {
        $dayNames = $this->journalDayNames();

        foreach ($assignments as $assignment) {
            $assignmentJournals = $weekJournals->get($assignment->id, collect());
            $scheduleStatuses = [];

            foreach ($assignment->schedules as $schedule) {
                $scheduleDayNumber = $schedule->day_of_week;
                $scheduleDate = $weekStart->copy()->addDays($scheduleDayNumber - 1)->format('Y-m-d');
                $scheduleDayName = $dayNames[$scheduleDayNumber] ?? 'Hari '.$scheduleDayNumber;
                $periodLabel = ($schedule->startPeriod && $schedule->endPeriod)
                    ? "Jam {$schedule->startPeriod->period_number} sd {$schedule->endPeriod->period_number}"
                    : null;

                $isFilled = $assignmentJournals->first(function (ClassJournal $journal) use ($scheduleDate) {
                    $journalDate = $journal->journal_date instanceof Carbon
                        ? $journal->journal_date->format('Y-m-d')
                        : substr((string) $journal->journal_date, 0, 10);

                    return $journalDate === $scheduleDate;
                }) !== null;

                $isToday = ($scheduleDate === $todayStr);
                $isPast = ($scheduleDate < $todayStr);
                $formattedDate = Carbon::parse($scheduleDate)->format('d/m');

                if ($isFilled) {
                    if ($isToday) {
                        $code = 'today_filled';
                        $label = '✅ Hari Ini: Selesai Diisi';
                        if ($periodLabel) {
                            $label .= " ({$periodLabel})";
                        }
                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        $priority = 3;
                    } else {
                        $code = 'past_filled';
                        $label = "✅ Sudah Diisi ({$scheduleDayName}, {$formattedDate})";
                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        $priority = 5;
                    }
                } elseif ($isToday) {
                    $code = 'today_unfilled';
                    $label = $periodLabel ? "⭐ Hari Ini: {$periodLabel} (Belum Diisi)" : '⭐ Hari Ini: Belum Diisi';
                    $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold';
                    $priority = 2;
                } elseif ($isPast) {
                    $code = 'overdue';
                    $label = "⚠️ Terlewat: {$scheduleDayName}, {$formattedDate} (Belum Diisi)";
                    $badgeClass = 'bg-amber-50 text-amber-900 border-amber-300 font-bold';
                    $priority = 1;
                } else {
                    $code = 'upcoming';
                    $label = "📅 Jadwal: {$scheduleDayName}, {$formattedDate}";
                    if ($periodLabel) {
                        $label .= " ({$periodLabel})";
                    }
                    $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                    $priority = 4;
                }

                $scheduleStatuses[] = [
                    'schedule_id' => $schedule->id,
                    'day_of_week' => $scheduleDayNumber,
                    'day_name' => $scheduleDayName,
                    'date' => $scheduleDate,
                    'formatted_date' => $formattedDate,
                    'period_label' => $periodLabel,
                    'is_filled' => $isFilled,
                    'code' => $code,
                    'label' => $label,
                    'badge_class' => $badgeClass,
                    'priority' => $priority,
                ];
            }

            usort($scheduleStatuses, fn (array $a, array $b) => $a['priority'] <=> $b['priority']);

            if ($scheduleStatuses !== []) {
                $primary = $scheduleStatuses[0];
                $assignment->schedule_summary = [
                    'primary_date' => $primary['date'],
                    'status_code' => $primary['code'],
                    'status_label' => $primary['label'],
                    'badge_class' => $primary['badge_class'],
                    'is_today' => in_array($primary['code'], ['today_unfilled', 'today_filled'], true),
                    'is_overdue' => ($primary['code'] === 'overdue'),
                    'all_schedules' => $scheduleStatuses,
                ];

                continue;
            }

            $assignment->schedule_summary = [
                'primary_date' => $isCurrentWeek ? $todayStr : $weekStart->format('Y-m-d'),
                'status_code' => 'no_schedule',
                'status_label' => 'Belum Ada Jadwal',
                'badge_class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'is_today' => false,
                'is_overdue' => false,
                'all_schedules' => [],
            ];
        }
    }

    /**
     * Penjaga pengisian absensi. Tanpa jadwal cocok, formulir dinonaktifkan.
     *
     * @return array{blocked: bool, tone: string, title: string, detail: string}
     */
    protected function journalAttendanceGate(
        string $selectedDate,
        ?TeachingSchedule $matchedSchedule,
        bool $requireScheduleMatch
    ): array {
        $dateLabel = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y');

        if ($selectedDate > now()->format('Y-m-d')) {
            return [
                'blocked' => true,
                'tone' => 'amber',
                'title' => "Tanggal di Masa Depan ({$dateLabel})",
                'detail' => 'Pengisian jurnal kelas hanya dapat dilakukan pada hari pelaksanaan mengajar atau untuk melengkapi jurnal yang terlewat. Form pengisian dinonaktifkan untuk tanggal di masa depan.',
            ];
        }

        if ($matchedSchedule) {
            return ['blocked' => false, 'tone' => 'none', 'title' => '', 'detail' => ''];
        }

        if (! $requireScheduleMatch) {
            return ['blocked' => false, 'tone' => 'none', 'title' => '', 'detail' => ''];
        }

        return [
            'blocked' => true,
            'tone' => 'rose',
            'title' => "Di Luar Jadwal Mengajar ({$dateLabel})",
            'detail' => 'Absensi hanya dapat diisi pada hari yang sesuai dengan jadwal mengajar Anda. Pilih tanggal lain yang tercantum pada jadwal untuk mengisi absensi.',
        ];
    }

    /**
     * Informasi tambahan saat mengisi jurnal jadwal yang terlewat.
     *
     * @return array{title: string, detail: string}|null
     */
    protected function journalLateNotice(string $selectedDate, bool $hasFilledJournalOnDate): ?array
    {
        if ($hasFilledJournalOnDate || $selectedDate >= now()->format('Y-m-d')) {
            return null;
        }

        $dateLabel = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y');

        return [
            'title' => "Melengkapi Jurnal Terlewat ({$dateLabel})",
            'detail' => 'Anda sedang membuka tanggal jadwal lampau. Silakan isi form di bawah untuk melengkapi jurnal dan absensi mengajar yang belum sempat diisi.',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function journalDayNames(): array
    {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    }

    /**
     * Tanggal dari query string; falls back ke hari ini bila kosong atau tak valid
     * agar input sampah tidak menyebabkan 500.
     */
    protected function journalDateQuery(mixed $value): string
    {
        return $this->journalOptionalDateQuery($value) ?? now()->format('Y-m-d');
    }

    /**
     * Tanggal opsional dari query string; null bila kosong atau format tak valid.
     */
    protected function journalOptionalDateQuery(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return $value;
    }

    /**
     * Jam pelajaran default: jadwal hari tersebut bila belum terisi, jika tidak jam kosong pertama.
     *
     * @param  Collection<int, LessonPeriod>  $lessonPeriods
     * @param  array<int, array<string, mixed>>  $occupiedPeriods
     * @return array{0: int|null, 1: int|null}
     */
    protected function resolveJournalDefaultPeriods(
        Collection $lessonPeriods,
        array $occupiedPeriods,
        ?TeachingSchedule $matchedSchedule
    ): array {
        $scheduledStart = $matchedSchedule?->startPeriod?->period_number;

        if ($matchedSchedule && ! isset($occupiedPeriods[$scheduledStart])) {
            return [$matchedSchedule->start_period_id, $matchedSchedule->end_period_id];
        }

        $firstAvailable = $lessonPeriods->first(fn (LessonPeriod $period) => ! isset($occupiedPeriods[$period->period_number]));

        if (! $firstAvailable) {
            return [null, null];
        }

        $nextAvailable = $lessonPeriods->first(fn (LessonPeriod $period) => $period->period_number === $firstAvailable->period_number + 1
            && ! isset($occupiedPeriods[$period->period_number]));

        return [$firstAvailable->id, $nextAvailable?->id ?? $firstAvailable->id];
    }

    /**
     * Jadwal penugasan mengajar yang jatuh pada tanggal tertentu.
     */
    protected function journalScheduleForDate(TeachingAssignment $assignment, string $date): ?TeachingSchedule
    {
        return $assignment->schedules->firstWhere('day_of_week', Carbon::parse($date)->dayOfWeekIso);
    }

    /**
     * Aturan validasi formulir jurnal kelas.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function journalAttendanceRules(bool $restrictAbsenceStatuses = false): array
    {
        return [
            'teaching_assignment_id' => ['required', 'integer', 'exists:teaching_assignments,id'],
            'journal_date' => ['required', 'date', 'before_or_equal:today'],
            'start_period_id' => ['required', 'exists:lesson_periods,id'],
            'end_period_id' => ['required', 'exists:lesson_periods,id'],
            'material' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'hadir_count' => ['nullable', 'integer', 'min:0'],
            'sakit_count' => ['nullable', 'integer', 'min:0'],
            'izin_count' => ['nullable', 'integer', 'min:0'],
            'alpha_count' => ['nullable', 'integer', 'min:0'],
            'absences' => ['nullable', 'array'],
            'absences.*.student_id' => ['required_with:absences', 'exists:student_profiles,id'],
            'absences.*.status' => $restrictAbsenceStatuses
                ? ['required_with:absences', 'string', Rule::in(self::$journalAllowedAbsenceStatuses)]
                : ['required_with:absences', 'string'],
            'absences.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Pesan validasi formulir jurnal kelas.
     *
     * @return array<string, string>
     */
    protected function journalAttendanceMessages(bool $restrictAbsenceStatuses = false): array
    {
        return array_filter([
            'journal_date.before_or_equal' => 'Pengisian jurnal hanya dapat dilakukan untuk hari ini atau tanggal lampau, tidak dapat mengisi tanggal di masa depan.',
            'absences.*.status.in' => $restrictAbsenceStatuses
                ? 'Status kehadiran harus salah satu dari: Izin, Sakit, atau Alpha.'
                : null,
        ]);
    }

    /**
     * Pastikan rentang jam pilihan bukan jam istirahat dan tidak bertabrakan.
     *
     * @return array<string, string>|null
     */
    protected function journalPeriodConflict(
        int $classId,
        string $date,
        LessonPeriod $startPeriod,
        LessonPeriod $endPeriod,
        ?int $exceptJournalId = null
    ): ?array {
        if ($startPeriod->is_break || $endPeriod->is_break) {
            return ['start_period_id' => 'Jam pelajaran yang dipilih tidak boleh berupa jam istirahat.'];
        }

        $minPeriod = min($startPeriod->period_number, $endPeriod->period_number);
        $maxPeriod = max($startPeriod->period_number, $endPeriod->period_number);

        $hasOverlap = ClassJournal::whereHas('teachingAssignment', fn (Builder $query) => $query->where('class_id', $classId))
            ->when($exceptJournalId, fn (Builder $query) => $query->where('id', '!=', $exceptJournalId))
            ->whereDate('journal_date', $date)
            ->where(function (Builder $query) use ($minPeriod, $maxPeriod) {
                $query->whereHas('startPeriod', fn (Builder $sp) => $sp->where('period_number', '<=', $maxPeriod))
                    ->whereHas('endPeriod', fn (Builder $ep) => $ep->where('period_number', '>=', $minPeriod));
            })
            ->exists();

        return $hasOverlap
            ? ['start_period_id' => 'Jam pelajaran yang dipilih bertabrakan dengan jurnal jam pelajaran lain yang sudah terisi pada kelas ini.']
            : null;
    }

    /**
     * Pastikan absensi hanya diisi untuk siswa aktif di kelas tersebut.
     *
     * @param  array<int, array<string, mixed>>  $absences
     * @return array<string, string>|null
     */
    protected function journalAttendanceEnrollmentConflict(int $classId, array $absences): ?array
    {
        $enrolledStudentIds = ClassEnrollment::query()
            ->where('class_id', $classId)
            ->where('status', 'ACTIVE')
            ->pluck('student_id')
            ->all();

        $submittedStudentIds = array_values(array_filter(array_map(
            fn (array $absence) => $absence['student_id'] ?? null,
            $absences,
        )));

        return array_diff(array_unique($submittedStudentIds), $enrolledStudentIds) !== []
            ? ['absences' => 'Absensi hanya dapat diisi untuk siswa yang terdaftar aktif di kelas ini.']
            : null;
    }

    /**
     * Catatan kelas digabung dengan ringkasan rekap kehadiran.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function journalNotesWithSummary(?string $notes, array $validated): string
    {
        $countsSummary = sprintf(
            'Hadir: %d | Sakit: %d | Izin: %d | Alpha: %d',
            $validated['hadir_count'] ?? 0,
            $validated['sakit_count'] ?? 0,
            $validated['izin_count'] ?? 0,
            $validated['alpha_count'] ?? 0
        );

        return $notes ? ($notes."\n".$countsSummary) : $countsSummary;
    }

    /**
     * Tulis ulang rincian kehadiran siswa pada sebuah jurnal.
     *
     * @param  array<int, array<string, mixed>>  $absences
     */
    protected function syncJournalAttendances(ClassJournal $journal, array $absences): void
    {
        $journal->attendances()->delete();

        $processedStudentIds = [];

        foreach ($absences as $absence) {
            $studentId = $absence['student_id'] ?? null;

            if (empty($studentId) || in_array($studentId, $processedStudentIds, true)) {
                continue;
            }

            $processedStudentIds[] = $studentId;

            JournalAttendance::create([
                'journal_id' => $journal->id,
                'student_id' => $studentId,
                'status' => $this->journalAbsenceStatus($absence['status'] ?? ''),
                'note' => $absence['note'] ?? null,
            ]);
        }
    }

    protected function journalAbsenceStatus(string $status): AttendanceStatus
    {
        return match (strtoupper($status)) {
            'SAKIT', 'S', 'SICK' => AttendanceStatus::Sick,
            'IZIN', 'I', 'PERMIT', 'PERMITTED' => AttendanceStatus::Permit,
            'ALPHA', 'A', 'ABSENT' => AttendanceStatus::Absent,
            default => AttendanceStatus::Present,
        };
    }

    protected function journalAbsenceLabel(AttendanceStatus $status): string
    {
        return match ($status) {
            AttendanceStatus::Sick => 'SAKIT',
            AttendanceStatus::Permit => 'IZIN',
            AttendanceStatus::Absent => 'ALPHA',
            default => 'HADIR',
        };
    }
}
