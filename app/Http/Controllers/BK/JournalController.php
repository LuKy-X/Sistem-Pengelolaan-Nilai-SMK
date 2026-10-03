<?php

namespace App\Http\Controllers\BK;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\BK\Concerns\ResolvesCounselorClasses;
use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JournalController extends Controller
{
    use ResolvesCounselorClasses;

    /**
     * Status absensi yang diterima dari form, sudah dinormalisasi ke huruf besar.
     *
     * @var list<string>
     */
    protected const ALLOWED_ABSENCE_STATUSES = [
        'SAKIT', 'IZIN', 'ALPHA',
        'S', 'I', 'A',
        'SICK', 'PERMIT', 'PERMITTED', 'ABSENT',
    ];

    public function index(Request $request): View
    {
        $counselorClasses = $this->counselorClasses()->load([
            'gradeLevel',
            'department',
        ]);

        $selectedClassId = $request->query('class_id');
        $selectedClass = $selectedClassId
            ? $counselorClasses->firstWhere('id', $selectedClassId)
            : null;

        $selectedDate = $request->query('date', now()->format('Y-m-d'));

        $journals = collect();
        $enrolledStudents = collect();
        $lessonPeriods = LessonPeriod::where('is_break', false)
            ->orderBy('period_number')
            ->get();
        $occupiedPeriods = [];
        $previousJournalAttendances = collect();
        $previousJournalInfo = null;
        $defaultStartPeriodId = null;
        $defaultEndPeriodId = null;

        if ($selectedClass) {
            // Jurnal kelas yang diampu pada tanggal terpilih, urut dari jam pelajaran pertama.
            $journals = $this->journalsForClassAndDate($selectedClass->id, $selectedDate);

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
                ->where('class_id', $selectedClass->id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name')
                ->values();

            // Jurnal sebelumnya pada kelas yang sama untuk menyalin rekap kehadiran.
            $latestJournal = $journals->last() ?? ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
                ->whereHas('teachingAssignment', fn (Builder $query) => $query->where('class_id', $selectedClass->id))
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
                    ->filter(fn ($attendance) => $attendance->status !== AttendanceStatus::Present)
                    ->map(fn ($attendance) => [
                        'student_id' => $attendance->student_id,
                        'student_name' => $attendance->student?->full_name ?? ('Siswa #'.$attendance->student_id),
                        'status' => match ($attendance->status) {
                            AttendanceStatus::Sick => 'SAKIT',
                            AttendanceStatus::Permit => 'IZIN',
                            AttendanceStatus::Absent => 'ALPHA',
                            default => 'HADIR',
                        },
                        'note' => $attendance->note ?? '',
                    ])
                    ->values();
            }

            [$defaultStartPeriodId, $defaultEndPeriodId] = $this->resolveDefaultPeriods($lessonPeriods, $occupiedPeriods);
        }

        return view('bk.journals.index', compact(
            'counselorClasses',
            'selectedClass',
            'selectedDate',
            'journals',
            'enrolledStudents',
            'lessonPeriods',
            'occupiedPeriods',
            'previousJournalAttendances',
            'previousJournalInfo',
            'defaultStartPeriodId',
            'defaultEndPeriodId',
        ));
    }

    /**
     * Seluruh jurnal kelas yang diampu Guru BK dengan penyaring kelas, rentang tanggal, dan pencarian.
     */
    public function history(Request $request): View
    {
        $counselorClasses = $this->counselorClasses()->load(['gradeLevel', 'department']);
        $classIds = $counselorClasses->pluck('id')->all();

        $classFilter = $request->query('class_id');
        $selectedClass = $classFilter !== null && $classFilter !== ''
            ? $counselorClasses->firstWhere('id', $classFilter)
            : null;
        $search = trim((string) $request->query('q', ''));
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');

        $journals = ClassJournal::query()
            ->withActiveClassStudentCount()
            ->with([
                'startPeriod',
                'endPeriod',
                'creator.user',
                'teachingAssignment.subject',
                'teachingAssignment.schoolClass',
                'attendances',
            ])
            ->whereHas('teachingAssignment', fn (Builder $query) => $query->whereIn('class_id', $classIds === [] ? [0] : $classIds))
            ->when($selectedClass !== null, fn (Builder $query) => $query
                ->whereHas('teachingAssignment', fn (Builder $inner) => $inner->where('class_id', $selectedClass->id)))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('journal_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('journal_date', '<=', $dateTo))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $inner) use ($search) {
                $inner->where('material', 'like', "%{$search}%")
                    ->orWhereHas('teachingAssignment.subject', fn (Builder $subject) => $subject->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('attendances.student', fn (Builder $student) => $student->where('full_name', 'like', "%{$search}%"));
            }))
            ->orderByDesc('journal_date')
            ->orderByDesc('start_period_id')
            ->paginate(15)
            ->withQueryString();

        // Rekap absensi dihitung dari jurnal pada halaman saat ini agar tidak memuat seluruh riwayat.
        $totals = ['sakit' => 0, 'izin' => 0, 'alpha' => 0];

        foreach ($journals as $journal) {
            $totals['sakit'] += $journal->sakit_count;
            $totals['izin'] += $journal->izin_count;
            $totals['alpha'] += $journal->alpha_count;
        }

        return view('bk.journals.history', [
            'counselorClasses' => $counselorClasses,
            'selectedClass' => $selectedClass,
            'journals' => $journals,
            'totals' => $totals,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Rincian absensi satu jurnal kelas yang diampu Guru BK.
     */
    public function show(ClassJournal $journal): View
    {
        abort_unless(
            in_array($journal->teachingAssignment?->class_id, $this->counselorClassIds(), true),
            403,
            'Anda tidak memiliki akses ke jurnal kelas ini.'
        );

        $journal->load([
            'startPeriod',
            'endPeriod',
            'creator.user',
            'teachingAssignment.subject',
            'teachingAssignment.schoolClass.department',
            'attendances.student.currentEnrollment.schoolClass',
        ]);

        return view('bk.journals.show', compact('journal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer'],
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
            'absences.*.status' => ['required_with:absences', 'string', Rule::in(self::ALLOWED_ABSENCE_STATUSES)],
            'absences.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'journal_date.before_or_equal' => 'Pengisian jurnal hanya dapat dilakukan untuk hari ini atau tanggal lampau, tidak dapat mengisi tanggal di masa depan.',
            'absences.*.status.in' => 'Status kehadiran harus salah satu dari: Izin, Sakit, atau Alpha.',
        ]);

        // Jurnal hanya boleh diisi untuk kelas yang diampu Guru BK.
        $classIds = $this->counselorClassIds();
        $schoolClass = SchoolClass::findOrFail($validated['class_id']);

        if (! in_array($schoolClass->id, $classIds)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        $startPeriod = LessonPeriod::findOrFail($validated['start_period_id']);
        $endPeriod = LessonPeriod::findOrFail($validated['end_period_id']);

        if ($startPeriod->is_break || $endPeriod->is_break) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih tidak boleh berupa jam istirahat.',
            ]);
        }

        $minPeriod = min($startPeriod->period_number, $endPeriod->period_number);
        $maxPeriod = max($startPeriod->period_number, $endPeriod->period_number);

        $hasOverlap = ClassJournal::whereHas('teachingAssignment', fn (Builder $query) => $query->where('class_id', $schoolClass->id))
            ->whereDate('journal_date', $validated['journal_date'])
            ->where(function (Builder $query) use ($minPeriod, $maxPeriod) {
                $query->whereHas('startPeriod', fn (Builder $sp) => $sp->where('period_number', '<=', $maxPeriod))
                    ->whereHas('endPeriod', fn (Builder $ep) => $ep->where('period_number', '>=', $minPeriod));
            })
            ->exists();

        if ($hasOverlap) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih bertabrakan dengan jurnal jam pelajaran lain yang sudah terisi pada kelas ini.',
            ]);
        }

        // Absensi hanya boleh diisi untuk siswa aktif yang terdaftar di kelas yang diampu.
        $enrolledStudentIds = ClassEnrollment::query()
            ->where('class_id', $schoolClass->id)
            ->where('status', 'ACTIVE')
            ->pluck('student_id')
            ->all();

        $submittedStudentIds = array_values(array_filter(array_map(
            fn (array $absence) => $absence['student_id'] ?? null,
            $validated['absences'] ?? [],
        )));

        if (array_diff(array_unique($submittedStudentIds), $enrolledStudentIds) !== []) {
            return redirect()->back()->withInput()->withErrors([
                'absences' => 'Absensi hanya dapat diisi untuk siswa yang terdaftar aktif di kelas ini.',
            ]);
        }

        $assignment = $this->resolveTeachingAssignment($schoolClass);

        if (! $assignment) {
            return back()->withInput()->with('error', 'Tidak ada penugasan mengajar aktif untuk kelas ini. Hubungi admin untuk mengatur jadwal mengajar.');
        }

        DB::transaction(function () use ($validated, $assignment) {
            $notesContent = $validated['notes'] ?? '';
            $countsSummary = sprintf(
                'Hadir: %d | Sakit: %d | Izin: %d | Alpha: %d',
                $validated['hadir_count'] ?? 0,
                $validated['sakit_count'] ?? 0,
                $validated['izin_count'] ?? 0,
                $validated['alpha_count'] ?? 0
            );
            $finalNotes = $notesContent ? ($notesContent."\n".$countsSummary) : $countsSummary;

            // Jurnal BK tercatat atas nama profil guru BK bila tersedia, jika tidak memakai guru pengajar.
            $counselorTeacherProfile = auth()->user()->teacherProfile ?? $assignment->teacher;

            $journal = ClassJournal::create([
                'teaching_assignment_id' => $assignment->id,
                'journal_date' => $validated['journal_date'],
                'start_period_id' => $validated['start_period_id'],
                'end_period_id' => $validated['end_period_id'],
                'material' => $validated['material'],
                'notes' => $finalNotes,
                'created_by' => $counselorTeacherProfile?->id ?? $assignment->teacher_id,
            ]);

            $processedStudentIds = [];

            foreach ($validated['absences'] ?? [] as $absence) {
                $studentId = $absence['student_id'] ?? null;

                if (empty($studentId) || in_array($studentId, $processedStudentIds, true)) {
                    continue;
                }

                $processedStudentIds[] = $studentId;

                $statusEnum = match (strtoupper($absence['status'])) {
                    'SAKIT', 'S', 'SICK' => AttendanceStatus::Sick,
                    'IZIN', 'I', 'PERMIT', 'PERMITTED' => AttendanceStatus::Permit,
                    'ALPHA', 'A', 'ABSENT' => AttendanceStatus::Absent,
                    default => AttendanceStatus::Present,
                };

                JournalAttendance::create([
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'status' => $statusEnum,
                    'note' => $absence['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('counselor.journals.index', [
            'class_id' => $validated['class_id'],
            'date' => $validated['journal_date'],
        ])->with('success', 'Jurnal kelas dan absensi berhasil disimpan.');
    }

    /**
     * @return Collection<int, ClassJournal>
     */
    protected function journalsForClassAndDate(int $classId, string $date): Collection
    {
        return ClassJournal::withActiveClassStudentCount()
            ->with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
            ->whereHas('teachingAssignment', fn (Builder $query) => $query->where('class_id', $classId))
            ->whereDate('journal_date', $date)
            ->get()
            ->sortBy(fn (ClassJournal $journal) => $journal->startPeriod?->period_number ?? 0)
            ->values();
    }

    /**
     * Sarankan rentang jam pelajaran yang belum terisi jurnal pada tanggal terpilih.
     *
     * @param  Collection<int, LessonPeriod>  $lessonPeriods
     * @param  array<int, array<string, mixed>>  $occupiedPeriods
     * @return array{0: int|null, 1: int|null}
     */
    protected function resolveDefaultPeriods(Collection $lessonPeriods, array $occupiedPeriods): array
    {
        $firstAvailable = $lessonPeriods->first(fn (LessonPeriod $period) => ! isset($occupiedPeriods[$period->period_number]));

        if (! $firstAvailable) {
            return [null, null];
        }

        $nextAvailable = $lessonPeriods->first(fn (LessonPeriod $period) => $period->period_number === $firstAvailable->period_number + 1
            && ! isset($occupiedPeriods[$period->period_number]));

        return [$firstAvailable->id, $nextAvailable?->id ?? $firstAvailable->id];
    }

    /**
     * Penugasan mengajar aktif milik kelas; bila lebih dari satu ambil yang pertama.
     */
    protected function resolveTeachingAssignment(SchoolClass $schoolClass): ?TeachingAssignment
    {
        return $schoolClass->teachingAssignments()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
