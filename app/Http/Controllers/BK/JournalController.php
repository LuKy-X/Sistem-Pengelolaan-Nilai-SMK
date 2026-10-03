<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\BK\Concerns\ResolvesCounselorClasses;
use App\Http\Controllers\Concerns\ManagesTeachingJournals;
use App\Http\Controllers\Controller;
use App\Models\ClassJournal;
use App\Models\LessonPeriod;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Absensi Kelas untuk Guru BK.
 *
 * Pengisian absensi memakai alur yang sama dengan Guru pengajar: kelas berasal dari
 * penugasan mengajar milik Guru BK sendiri, dan pengisian hanya dapat dilakukan pada
 * hari yang sesuai jadwal mengajar. Selain itu Guru BK dapat melihat seluruh absensi
 * kelas binaannya secara read-only.
 */
class JournalController extends Controller
{
    use ManagesTeachingJournals;
    use ResolvesCounselorClasses;

    public function index(Request $request): View
    {
        return view('bk.journals.index', $this->teachingJournalWorkspace(
            $request,
            $this->journalTeacherProfile(),
            [
                'routes' => [
                    'index' => 'counselor.journals.index',
                    'store' => 'counselor.journals.store',
                    'update' => 'counselor.journals.update',
                    'destroy' => 'counselor.journals.destroy',
                ],
                'requireScheduleMatch' => true,
                'showExportMenu' => false,
                'subheading' => 'Isi absensi kelas pada hari sesuai jadwal mengajar Anda. Untuk melihat absensi kelas lain, buka tab Lihat Absensi.',
                'emptyMessage' => 'Belum ada penugasan mengajar aktif untuk Anda. Hubungi administrator/kurikulum untuk penugasan mengajar dan jadwal.',
            ]
        ));
    }

    /**
     * Tab read-only: absensi kelas binaaan sekaligus kelas yang diajar Guru BK.
     *
     * Tidak memerlukan jadwal hari itu: semua sesi yang tercatat pada tanggal
     * terpilih ditampilkan, sehingga BK tetap bisa memantau kelas binaannya
     * pada hari ketika dia tidak mengajar.
     */
    public function attendance(Request $request): View
    {
        // Read-only: menampilkan kelas binaaan sekaligus kelas yang diajar BK,
        // sehingga absensi tetap terlihat meski hari itu tidak ada jadwalnya.
        $visibleClasses = $this->counselorVisibleClasses()->load([
            'gradeLevel',
            'department',
        ]);

        // Tanpa pilihan kelas, tampilkan seluruh kelas binaaan + yang diajar.
        $selectedClassId = $request->query('class_id');
        $selectedClass = $selectedClassId !== null && $selectedClassId !== ''
            ? $visibleClasses->firstWhere('id', $selectedClassId)
            : null;

        $selectedDate = $this->journalDateQuery($request->query('date'));
        $scopeClassId = $selectedClass?->id;
        $classIds = $visibleClasses->pluck('id')->all();

        $journals = ClassJournal::withActiveClassStudentCount()
            ->with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
                'teachingAssignment.schoolClass',
            ])
            ->whereHas('teachingAssignment', fn (Builder $query) => $query->whereIn('class_id', $classIds === [] ? [0] : $classIds))
            ->when($scopeClassId !== null, fn (Builder $query) => $query
                ->whereHas('teachingAssignment', fn (Builder $inner) => $inner->where('class_id', $scopeClassId)))
            ->whereDate('journal_date', $selectedDate)
            ->get()
            ->sortBy([
                fn (ClassJournal $journal) => $journal->teachingAssignment?->schoolClass?->name ?? '',
                fn (ClassJournal $journal) => $journal->startPeriod?->period_number ?? 0,
            ])
            ->values();

        $totals = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];

        foreach ($journals as $journal) {
            $totals['hadir'] += $journal->hadir_count;
            $totals['sakit'] += $journal->sakit_count;
            $totals['izin'] += $journal->izin_count;
            $totals['alpha'] += $journal->alpha_count;
        }

        return view('bk.journals.attendance', [
            'visibleClasses' => $visibleClasses,
            'selectedClass' => $selectedClass,
            'selectedDate' => $selectedDate,
            'journals' => $journals,
            'totals' => $totals,
            'prevDate' => Carbon::parse($selectedDate)->subDay()->format('Y-m-d'),
            'nextDate' => Carbon::parse($selectedDate)->addDay()->format('Y-m-d'),
            'today' => now()->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'weekStartDate' => now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
        ]);
    }

    /**
     * Seluruh jurnal kelas yang diampu Guru BK dengan penyaring kelas, rentang tanggal, dan pencarian.
     */
    public function history(Request $request): View
    {
        // Sama seperti tab Lihat Absensi: kelas binaaan + kelas yang diajar BK.
        $visibleClasses = $this->counselorVisibleClasses()->load(['gradeLevel', 'department']);
        $classIds = $visibleClasses->pluck('id')->all();

        $classFilter = $request->query('class_id');
        $selectedClass = $classFilter !== null && $classFilter !== ''
            ? $visibleClasses->firstWhere('id', $classFilter)
            : null;
        $search = trim((string) $request->query('q', ''));
        $dateFrom = $this->journalOptionalDateQuery($request->query('date_from'));
        $dateTo = $this->journalOptionalDateQuery($request->query('date_to'));

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
            ->when($dateFrom !== null, fn (Builder $query) => $query->whereDate('journal_date', '>=', $dateFrom))
            ->when($dateTo !== null, fn (Builder $query) => $query->whereDate('journal_date', '<=', $dateTo))
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
            'counselorClasses' => $visibleClasses,
            'selectedClass' => $selectedClass,
            'journals' => $journals,
            'totals' => $totals,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Rincian absensi satu jurnal kelas (read-only).
     */
    public function show(ClassJournal $journal): View
    {
        $teacherProfileId = $this->journalTeacherProfile()?->id;

        abort_unless(
            in_array($journal->teachingAssignment?->class_id, $this->counselorClassIds(), true)
                || $journal->created_by === $teacherProfileId,
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
        $teacher = $this->journalTeacherProfile();

        $validated = $request->validate(
            $this->journalAttendanceRules(true),
            $this->journalAttendanceMessages(true)
        );

        $assignment = TeachingAssignment::with('schedules')
            ->findOrFail($validated['teaching_assignment_id']);

        abort_unless(
            $assignment->teacher_id === $teacher?->id,
            403,
            'Penugasan mengajar ini bukan milik Anda.'
        );

        // Absensi hanya boleh diisi pada hari yang sesuai jadwal mengajar Guru BK.
        $schedule = $this->journalScheduleForDate($assignment, $validated['journal_date']);

        if (! $schedule) {
            return redirect()->back()->withInput()->withErrors([
                'journal_date' => $this->scheduleMismatchMessage($assignment),
            ]);
        }

        $startPeriod = LessonPeriod::findOrFail($validated['start_period_id']);
        $endPeriod = LessonPeriod::findOrFail($validated['end_period_id']);

        $conflict = $this->journalPeriodConflict($assignment->class_id, $validated['journal_date'], $startPeriod, $endPeriod)
            ?? $this->journalAttendanceEnrollmentConflict($assignment->class_id, $validated['absences'] ?? []);

        if ($conflict) {
            return redirect()->back()->withInput()->withErrors($conflict);
        }

        DB::transaction(function () use ($validated, $assignment, $schedule, $teacher) {
            $journal = ClassJournal::create([
                'teaching_assignment_id' => $assignment->id,
                'schedule_id' => $schedule->id,
                'journal_date' => $validated['journal_date'],
                'start_period_id' => $validated['start_period_id'],
                'end_period_id' => $validated['end_period_id'],
                'material' => $validated['material'],
                'notes' => $this->journalNotesWithSummary($validated['notes'] ?? '', $validated),
                'created_by' => $teacher->id,
            ]);

            $this->syncJournalAttendances($journal, $validated['absences'] ?? []);
        });

        return redirect()->route('counselor.journals.index', [
            'assignment_id' => $assignment->id,
            'date' => $validated['journal_date'],
        ])->with('success', 'Jurnal kelas dan absensi berhasil disimpan.');
    }

    public function update(Request $request, ClassJournal $journal): RedirectResponse
    {
        $teacher = $this->journalTeacherProfile();

        abort_unless(
            $journal->created_by === $teacher?->id,
            403,
            'Akses tidak diizinkan. Anda hanya dapat mengedit jurnal yang Anda buat sendiri.'
        );

        $journal->loadMissing('teachingAssignment.schedules');

        $validated = $request->validate([
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
            'absences.*.status' => ['required_with:absences', 'string', Rule::in(self::$journalAllowedAbsenceStatuses)],
            'absences.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'absences.*.status.in' => 'Status kehadiran harus salah satu dari: Izin, Sakit, atau Alpha.',
        ]);

        $startPeriod = LessonPeriod::findOrFail($validated['start_period_id']);
        $endPeriod = LessonPeriod::findOrFail($validated['end_period_id']);

        $classId = $journal->teachingAssignment->class_id;
        $journalDate = $journal->journal_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        $conflict = $this->journalPeriodConflict($classId, $journalDate, $startPeriod, $endPeriod, $journal->id)
            ?? $this->journalAttendanceEnrollmentConflict($classId, $validated['absences'] ?? []);

        if ($conflict) {
            return redirect()->back()->withInput()->withErrors($conflict);
        }

        DB::transaction(function () use ($validated, $journal, $journalDate) {
            $journal->update([
                'schedule_id' => $this->journalScheduleForDate($journal->teachingAssignment, $journalDate)?->id,
                'start_period_id' => $validated['start_period_id'],
                'end_period_id' => $validated['end_period_id'],
                'material' => $validated['material'],
                'notes' => $this->journalNotesWithSummary($validated['notes'] ?? '', $validated),
            ]);

            $this->syncJournalAttendances($journal, $validated['absences'] ?? []);
        });

        return redirect()->route('counselor.journals.index', [
            'assignment_id' => $journal->teaching_assignment_id,
            'date' => $journalDate,
        ])->with('success', 'Jurnal kelas dan absensi berhasil diperbarui.');
    }

    public function destroy(ClassJournal $journal): RedirectResponse
    {
        $teacher = $this->journalTeacherProfile();

        abort_unless(
            $journal->created_by === $teacher?->id,
            403,
            'Akses tidak diizinkan. Anda hanya dapat menghapus jurnal yang Anda buat sendiri.'
        );

        $assignmentId = $journal->teaching_assignment_id;
        $journalDate = $journal->journal_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        $journal->delete();

        return redirect()->route('counselor.journals.index', [
            'assignment_id' => $assignmentId,
            'date' => $journalDate,
        ])->with('success', 'Data jurnal kelas berhasil dihapus.');
    }

    /**
     * Pesan error saat tanggal absensi tidak sesuai jadwal mengajar.
     */
    protected function scheduleMismatchMessage(TeachingAssignment $assignment): string
    {
        if ($assignment->schedules->isEmpty()) {
            return 'Kelas ini belum memiliki jadwal mengajar sehingga absensi tidak dapat diisi. Hubungi administrator/kurikulum.';
        }

        $dayNames = $assignment->schedules
            ->map(fn ($schedule) => $this->journalDayNames()[$schedule->day_of_week] ?? 'Hari '.$schedule->day_of_week)
            ->unique()
            ->implode(', ');

        return "Absensi hanya dapat diisi pada hari sesuai jadwal mengajar Anda ({$dayNames}).";
    }
}
