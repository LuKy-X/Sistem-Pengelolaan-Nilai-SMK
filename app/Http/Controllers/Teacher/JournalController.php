<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.gradeLevel',
            'schoolClass.department',
            'subject',
            'semester.academicYear',
            'schedules.startPeriod',
            'schedules.endPeriod',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id');
        $selectedAssignment = $selectedAssignmentId ? $assignments->firstWhere('id', $selectedAssignmentId) : null;
        $selectedDate = $request->query('date', now()->format('Y-m-d'));

        $referenceDate = Carbon::parse($selectedDate);
        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);
        $todayStr = now()->format('Y-m-d');
        $isCurrentWeek = now()->betweenIncluded($weekStart, $weekEnd);
        $prevWeekDate = $weekStart->copy()->subWeek()->format('Y-m-d');
        $nextWeekDate = $weekStart->copy()->addWeek()->format('Y-m-d');
        $currentWeekDate = now()->format('Y-m-d');

        // Preload all journals created by this teacher for this week in 1 query
        $weekJournals = ClassJournal::whereIn('teaching_assignment_id', $assignments->pluck('id'))
            ->where('created_by', $teacher->id)
            ->whereBetween('journal_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get()
            ->groupBy('teaching_assignment_id');

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        foreach ($assignments as $assignment) {
            $assignmentJournals = $weekJournals->get($assignment->id, collect());
            $scheduleStatuses = [];

            foreach ($assignment->schedules as $sched) {
                $schedDayNumber = $sched->day_of_week;
                $schedDate = $weekStart->copy()->addDays($schedDayNumber - 1)->format('Y-m-d');
                $schedDayName = $dayNames[$schedDayNumber] ?? 'Hari '.$schedDayNumber;
                $periodLabel = ($sched->startPeriod && $sched->endPeriod)
                    ? "Jam {$sched->startPeriod->period_number} sd {$sched->endPeriod->period_number}"
                    : null;

                $isFilled = $assignmentJournals->first(function ($j) use ($schedDate) {
                    $jDate = $j->journal_date instanceof Carbon ? $j->journal_date->format('Y-m-d') : substr((string) $j->journal_date, 0, 10);

                    return $jDate === $schedDate;
                }) !== null;

                $isToday = ($schedDate === $todayStr);
                $isPast = ($schedDate < $todayStr);

                $formattedDate = Carbon::parse($schedDate)->format('d/m');

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
                        $label = "✅ Sudah Diisi ({$schedDayName}, {$formattedDate})";
                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        $priority = 5;
                    }
                } else {
                    if ($isToday) {
                        $code = 'today_unfilled';
                        $label = $periodLabel ? "⭐ Hari Ini: {$periodLabel} (Belum Diisi)" : '⭐ Hari Ini: Belum Diisi';
                        $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold';
                        $priority = 2;
                    } elseif ($isPast) {
                        $code = 'overdue';
                        $label = "⚠️ Terlewat: {$schedDayName}, {$formattedDate} (Belum Diisi)";
                        $badgeClass = 'bg-amber-50 text-amber-900 border-amber-300 font-bold';
                        $priority = 1;
                    } else {
                        $code = 'upcoming';
                        $label = "📅 Jadwal: {$schedDayName}, {$formattedDate}";
                        if ($periodLabel) {
                            $label .= " ({$periodLabel})";
                        }
                        $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                        $priority = 4;
                    }
                }

                $scheduleStatuses[] = [
                    'schedule_id' => $sched->id,
                    'day_of_week' => $schedDayNumber,
                    'day_name' => $schedDayName,
                    'date' => $schedDate,
                    'formatted_date' => $formattedDate,
                    'period_label' => $periodLabel,
                    'is_filled' => $isFilled,
                    'code' => $code,
                    'label' => $label,
                    'badge_class' => $badgeClass,
                    'priority' => $priority,
                ];
            }

            usort($scheduleStatuses, fn ($a, $b) => $a['priority'] <=> $b['priority']);

            if (! empty($scheduleStatuses)) {
                $primary = $scheduleStatuses[0];
                $assignment->schedule_summary = [
                    'primary_date' => $primary['date'],
                    'status_code' => $primary['code'],
                    'status_label' => $primary['label'],
                    'badge_class' => $primary['badge_class'],
                    'is_today' => in_array($primary['code'], ['today_unfilled', 'today_filled']),
                    'is_overdue' => ($primary['code'] === 'overdue'),
                    'all_schedules' => $scheduleStatuses,
                ];
            } else {
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

        $journals = collect();
        $enrolledStudents = collect();
        $lessonPeriods = LessonPeriod::orderBy('period_number')->get();
        $previousJournalAttendances = collect();
        $previousJournalInfo = null;
        $defaultStartPeriodId = null;
        $defaultEndPeriodId = null;

        if ($selectedAssignment) {
            // Journals displayed ONLY for the single selected day, ordered chronologically from period 1 to the end
            $journals = ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
                ->whereHas('teachingAssignment', function ($q) use ($selectedAssignment) {
                    $q->where('class_id', $selectedAssignment->class_id);
                })
                ->whereDate('journal_date', $selectedDate)
                ->get()
                ->sortBy(fn ($j) => $j->startPeriod?->period_number ?? 0)
                ->values();

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedAssignment->class_id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name')
                ->values();

            // Find previous journal entry for this class to allow copying attendance
            // Prefers the latest session from today if exists; otherwise falls back to the most recent historical session
            $latestJournal = $journals->last() ?? ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
                ->whereHas('teachingAssignment', function ($q) use ($selectedAssignment) {
                    $q->where('class_id', $selectedAssignment->class_id);
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
                    ->filter(fn ($att) => $att->status !== AttendanceStatus::Present)
                    ->map(function ($att) {
                        return [
                            'student_id' => $att->student_id,
                            'student_name' => $att->student?->full_name ?? ('Siswa #'.$att->student_id),
                            'status' => match ($att->status) {
                                AttendanceStatus::Sick => 'SAKIT',
                                AttendanceStatus::Permit => 'IZIN',
                                AttendanceStatus::Absent => 'ALPHA',
                                default => 'HADIR',
                            },
                            'note' => $att->note ?? '',
                        ];
                    })
                    ->values();
            }

            // Determine smart default start and end periods for today
            $dayOfWeekNumber = Carbon::parse($selectedDate)->dayOfWeekIso;
            $todaySchedule = $selectedAssignment->schedules->firstWhere('day_of_week', $dayOfWeekNumber);

            if ($todaySchedule) {
                $defaultStartPeriodId = $todaySchedule->start_period_id;
                $defaultEndPeriodId = $todaySchedule->end_period_id;
            } elseif ($journals->isNotEmpty()) {
                $lastPeriodNumber = $journals->last()->endPeriod?->period_number;
                if ($lastPeriodNumber) {
                    $nextStart = $lessonPeriods->firstWhere('period_number', $lastPeriodNumber + 1);
                    $nextEnd = $lessonPeriods->firstWhere('period_number', $lastPeriodNumber + 2) ?? $nextStart;
                    $defaultStartPeriodId = $nextStart?->id;
                    $defaultEndPeriodId = $nextEnd?->id;
                }
            }
        }

        return view('teacher.journals.index', compact(
            'assignments',
            'selectedAssignment',
            'selectedDate',
            'journals',
            'enrolledStudents',
            'lessonPeriods',
            'previousJournalAttendances',
            'previousJournalInfo',
            'defaultStartPeriodId',
            'defaultEndPeriodId',
            'weekStart',
            'weekEnd',
            'isCurrentWeek',
            'prevWeekDate',
            'nextWeekDate',
            'currentWeekDate'
        ));
    }

    public function create(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;
        $assignments = TeachingAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);
        $lessonPeriods = LessonPeriod::orderBy('period_number')->get();

        return view('teacher.journals.create', compact('assignments', 'selectedAssignmentId', 'lessonPeriods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
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
            'absences.*.status' => ['required_with:absences', 'string'],
            'absences.*.note' => ['nullable', 'string'],
        ], [
            'journal_date.before_or_equal' => 'Pengisian jurnal hanya dapat dilakukan untuk hari ini atau tanggal lampau, tidak dapat mengisi tanggal di masa depan.',
        ]);

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);
        if ($assignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        DB::transaction(function () use ($validated, $teacher) {
            $notesContent = $validated['notes'] ?? '';
            // Append summary counts to notes if provided
            $countsSummary = sprintf(
                'Hadir: %d | Sakit: %d | Izin: %d | Alpha: %d',
                $validated['hadir_count'] ?? 0,
                $validated['sakit_count'] ?? 0,
                $validated['izin_count'] ?? 0,
                $validated['alpha_count'] ?? 0
            );
            $finalNotes = $notesContent ? ($notesContent."\n".$countsSummary) : $countsSummary;

            $journal = ClassJournal::create([
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'journal_date' => $validated['journal_date'],
                'start_period_id' => $validated['start_period_id'],
                'end_period_id' => $validated['end_period_id'],
                'material' => $validated['material'],
                'notes' => $finalNotes,
                'created_by' => $teacher->id,
            ]);

            // Save individual absent/sick/permitted students if submitted
            if (! empty($validated['absences'])) {
                $processedStudentIds = [];
                foreach ($validated['absences'] as $abs) {
                    if (empty($abs['student_id']) || in_array($abs['student_id'], $processedStudentIds)) {
                        continue;
                    }
                    $processedStudentIds[] = $abs['student_id'];

                    $statusEnum = match (strtoupper($abs['status'])) {
                        'SAKIT', 'S', 'SICK' => AttendanceStatus::Sick,
                        'IZIN', 'I', 'PERMIT', 'PERMITTED' => AttendanceStatus::Permit,
                        'ALPHA', 'A', 'ABSENT' => AttendanceStatus::Absent,
                        default => AttendanceStatus::Present,
                    };

                    JournalAttendance::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status' => $statusEnum,
                        'note' => $abs['note'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('teacher.journals.index', [
            'assignment_id' => $validated['teaching_assignment_id'],
            'date' => $validated['journal_date'],
        ])->with('success', 'Jurnal kelas dan absensi berhasil disimpan.');
    }

    public function update(Request $request, ClassJournal $journal): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        if ($journal->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan. Anda hanya dapat mengedit jurnal yang Anda buat sendiri.');
        }

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
            'absences.*.status' => ['required_with:absences', 'string'],
            'absences.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $journal) {
            $notesContent = $validated['notes'] ?? '';
            $countsSummary = sprintf(
                'Hadir: %d | Sakit: %d | Izin: %d | Alpha: %d',
                $validated['hadir_count'] ?? 0,
                $validated['sakit_count'] ?? 0,
                $validated['izin_count'] ?? 0,
                $validated['alpha_count'] ?? 0
            );
            $finalNotes = $notesContent ? ($notesContent."\n".$countsSummary) : $countsSummary;

            $journal->update([
                'start_period_id' => $validated['start_period_id'],
                'end_period_id' => $validated['end_period_id'],
                'material' => $validated['material'],
                'notes' => $finalNotes,
            ]);

            // Re-sync individual attendances
            $journal->attendances()->delete();

            if (! empty($validated['absences'])) {
                $processedStudentIds = [];
                foreach ($validated['absences'] as $abs) {
                    if (empty($abs['student_id']) || in_array($abs['student_id'], $processedStudentIds)) {
                        continue;
                    }
                    $processedStudentIds[] = $abs['student_id'];

                    $statusEnum = match (strtoupper($abs['status'])) {
                        'SAKIT', 'S', 'SICK' => AttendanceStatus::Sick,
                        'IZIN', 'I', 'PERMIT', 'PERMITTED' => AttendanceStatus::Permit,
                        'ALPHA', 'A', 'ABSENT' => AttendanceStatus::Absent,
                        default => AttendanceStatus::Present,
                    };

                    JournalAttendance::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status' => $statusEnum,
                        'note' => $abs['note'] ?? null,
                    ]);
                }
            }
        });

        $dateStr = $journal->journal_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        return redirect()->route('teacher.journals.index', [
            'assignment_id' => $journal->teaching_assignment_id,
            'date' => $dateStr,
        ])->with('success', 'Jurnal kelas dan absensi berhasil diperbarui.');
    }

    public function destroy(ClassJournal $journal): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        if ($journal->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan. Anda hanya dapat menghapus jurnal yang Anda buat sendiri.');
        }

        $assignmentId = $journal->teaching_assignment_id;
        $dateStr = $journal->journal_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        $journal->delete();

        return redirect()->route('teacher.journals.index', [
            'assignment_id' => $assignmentId,
            'date' => $dateStr,
        ])->with('success', 'Data jurnal kelas berhasil dihapus.');
    }

    public function show(ClassJournal $journal): View
    {
        $journal->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'startPeriod',
            'endPeriod',
            'attendances.student',
            'creator',
        ]);

        return view('teacher.journals.show', compact('journal'));
    }
}
