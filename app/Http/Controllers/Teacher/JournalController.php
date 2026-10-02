<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\SchoolProfile;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $allDayPeriods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();
        $lessonPeriods = LessonPeriod::where('is_break', false)->orderBy('period_number')->get();
        $occupiedPeriods = [];
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

            foreach ($journals as $j) {
                $startNum = $j->startPeriod?->period_number;
                $endNum = $j->endPeriod?->period_number ?? $startNum;
                if ($startNum && $endNum) {
                    for ($p = min($startNum, $endNum); $p <= max($startNum, $endNum); $p++) {
                        $occupiedPeriods[$p] = [
                            'journal_id' => $j->id,
                            'period_number' => $p,
                            'subject' => $j->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                            'teacher' => $j->creator?->user?->name ?? 'Guru',
                        ];
                    }
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

            // Determine smart default start and end periods for today:
            // Prefer the scheduled periods if not occupied; otherwise pick the first available unfilled period
            $dayOfWeekNumber = Carbon::parse($selectedDate)->dayOfWeekIso;
            $todaySchedule = $selectedAssignment->schedules->firstWhere('day_of_week', $dayOfWeekNumber);

            if ($todaySchedule && ! isset($occupiedPeriods[$todaySchedule->startPeriod?->period_number])) {
                $defaultStartPeriodId = $todaySchedule->start_period_id;
                $defaultEndPeriodId = $todaySchedule->end_period_id;
            } else {
                $firstAvailable = $lessonPeriods->first(fn ($lp) => ! isset($occupiedPeriods[$lp->period_number]));
                if ($firstAvailable) {
                    $defaultStartPeriodId = $firstAvailable->id;
                    $secondAvailable = $lessonPeriods->first(fn ($lp) => $lp->period_number === $firstAvailable->period_number + 1 && ! isset($occupiedPeriods[$lp->period_number]));
                    $defaultEndPeriodId = $secondAvailable ? $secondAvailable->id : $firstAvailable->id;
                }
            }
        }

        return view('teacher.journals.index', compact(
            'assignments',
            'selectedAssignment',
            'selectedDate',
            'journals',
            'enrolledStudents',
            'allDayPeriods',
            'lessonPeriods',
            'occupiedPeriods',
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

        $startPeriod = LessonPeriod::findOrFail($validated['start_period_id']);
        $endPeriod = LessonPeriod::findOrFail($validated['end_period_id']);

        if ($startPeriod->is_break || $endPeriod->is_break) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih tidak boleh berupa jam istirahat.',
            ]);
        }

        $minPeriod = min($startPeriod->period_number, $endPeriod->period_number);
        $maxPeriod = max($startPeriod->period_number, $endPeriod->period_number);

        // Check overlap with existing journals for this class on this date
        $hasOverlap = ClassJournal::whereHas('teachingAssignment', function ($q) use ($assignment) {
            $q->where('class_id', $assignment->class_id);
        })
            ->whereDate('journal_date', $validated['journal_date'])
            ->where(function ($q) use ($minPeriod, $maxPeriod) {
                $q->whereHas('startPeriod', fn ($sp) => $sp->where('period_number', '<=', $maxPeriod))
                    ->whereHas('endPeriod', fn ($ep) => $ep->where('period_number', '>=', $minPeriod));
            })
            ->exists();

        if ($hasOverlap) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih bertabrakan dengan jurnal jam pelajaran lain yang sudah terisi pada kelas ini.',
            ]);
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

        $startPeriod = LessonPeriod::findOrFail($validated['start_period_id']);
        $endPeriod = LessonPeriod::findOrFail($validated['end_period_id']);

        if ($startPeriod->is_break || $endPeriod->is_break) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih tidak boleh berupa jam istirahat.',
            ]);
        }

        $minPeriod = min($startPeriod->period_number, $endPeriod->period_number);
        $maxPeriod = max($startPeriod->period_number, $endPeriod->period_number);

        $hasOverlap = ClassJournal::whereHas('teachingAssignment', function ($q) use ($journal) {
            $q->where('class_id', $journal->teachingAssignment->class_id);
        })
            ->where('id', '!=', $journal->id)
            ->whereDate('journal_date', $journal->journal_date)
            ->where(function ($q) use ($minPeriod, $maxPeriod) {
                $q->whereHas('startPeriod', fn ($sp) => $sp->where('period_number', '<=', $maxPeriod))
                    ->whereHas('endPeriod', fn ($ep) => $ep->where('period_number', '>=', $minPeriod));
            })
            ->exists();

        if ($hasOverlap) {
            return redirect()->back()->withInput()->withErrors([
                'start_period_id' => 'Jam pelajaran yang dipilih bertabrakan dengan jurnal jam pelajaran lain yang sudah terisi pada kelas ini.',
            ]);
        }

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

    /**
     * Export jurnal kelas ke format PDF (dokumen cetak A4 landscape).
     */
    public function exportPdf(Request $request): View
    {
        $data = $this->resolveExportData($request);

        return view('teacher.journals.pdf', $data);
    }

    /**
     * Export jurnal kelas ke format Excel (.xlsx Spreadsheet).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->resolveExportData($request);
        $assignment = $data['assignment'];
        $selectedDate = $data['selectedDate'];
        $range = $data['range'];
        $referenceDate = $data['referenceDate'];
        $weekStart = $data['weekStart'];
        $weekEnd = $data['weekEnd'];
        $allDayPeriods = $data['allDayPeriods'];
        $journals = $data['journals'];
        $enrolledStudents = $data['enrolledStudents'];
        $schoolProfile = $data['schoolProfile'];

        $schoolName = $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR';
        $className = $assignment->schoolClass?->name ?? 'Kelas';
        $subjectName = $assignment->subject?->name ?? 'Mata Pelajaran';
        $academicYear = $assignment->semester?->academicYear?->name ?? '2024/2025';
        $semesterName = $assignment->semester?->name ?? 'Ganjil';
        $homeroomTeacher = $assignment->schoolClass?->homeroomTeacher?->full_name ?? ($assignment->schoolClass?->homeroomTeacher?->user?->name ?? '-');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jurnal Kelas');

        // Page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Title Block
        $sheet->setCellValue('A1', 'BUKU AGENDA JURNAL PEMBELAJARAN & PRESENSI KELAS');
        $sheet->setCellValue('A2', strtoupper($schoolName));
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata block (Rows 4-6)
        $sheet->setCellValue('A4', 'Kelas');
        $sheet->setCellValue('B4', ': '.$className);
        $sheet->setCellValue('F4', 'Tahun Ajaran');
        $sheet->setCellValue('G4', ': '.$academicYear.' ('.$semesterName.')');

        $sheet->setCellValue('A5', 'Mata Pelajaran');
        $sheet->setCellValue('B5', ': '.$subjectName);
        $sheet->setCellValue('F5', 'Wali Kelas');
        $sheet->setCellValue('G5', ': '.$homeroomTeacher);

        $sheet->setCellValue('A6', 'Periode Rekap');
        if ($range === 'weekly') {
            $sheet->setCellValue('B6', ': Minggu Ke-'.$weekStart->isoWeek().' ('.$weekStart->format('d/m/Y').' - '.$weekEnd->format('d/m/Y').')');
        } else {
            $sheet->setCellValue('B6', ': '.$referenceDate->isoFormat('dddd, D MMMM Y'));
        }
        $sheet->setCellValue('F6', 'Total Siswa');
        $sheet->setCellValue('G6', ': '.$enrolledStudents->count().' Siswa');

        $sheet->getStyle('A4:G6')->getFont()->setSize(10);
        $sheet->getStyle('A4:A6')->getFont()->setBold(true);
        $sheet->getStyle('F4:F6')->getFont()->setBold(true);

        // Table Header (Rows 8-9)
        $headerRow1 = 8;
        $headerRow2 = 9;

        $sheet->setCellValue('A'.$headerRow1, 'Hari / Tanggal');
        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");

        $sheet->setCellValue('B'.$headerRow1, 'Jam Ke-');
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");

        $sheet->setCellValue('C'.$headerRow1, 'Mata Pelajaran');
        $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");

        $sheet->setCellValue('D'.$headerRow1, 'Guru Pengajar');
        $sheet->mergeCells("D{$headerRow1}:D{$headerRow2}");

        $sheet->setCellValue('E'.$headerRow1, 'Materi Pembelajaran');
        $sheet->mergeCells("E{$headerRow1}:E{$headerRow2}");

        $sheet->setCellValue('F'.$headerRow1, 'Jumlah Siswa');
        $sheet->mergeCells("F{$headerRow1}:I{$headerRow1}");
        $sheet->setCellValue('F'.$headerRow2, 'Hadir');
        $sheet->setCellValue('G'.$headerRow2, 'Sakit');
        $sheet->setCellValue('H'.$headerRow2, 'Izin');
        $sheet->setCellValue('I'.$headerRow2, 'Alpha');

        $sheet->setCellValue('J'.$headerRow1, 'Keterangan & Rincian Ketidakhadiran');
        $sheet->mergeCells("J{$headerRow1}:J{$headerRow2}");

        $currRow = 10;

        if ($range === 'weekly') {
            // Group by dates in week (Senin s.d. Sabtu)
            for ($d = 1; $d <= 6; $d++) {
                $curDate = $weekStart->copy()->addDays($d - 1);
                $curDateStr = $curDate->format('Y-m-d');
                $dayJournals = $journals->filter(function ($j) use ($curDateStr) {
                    $jDate = $j->journal_date instanceof Carbon ? $j->journal_date->format('Y-m-d') : substr((string) $j->journal_date, 0, 10);

                    return $jDate === $curDateStr;
                })->values();

                if ($dayJournals->isEmpty()) {
                    continue;
                }

                $currRow = $this->renderSpreadsheetDayRows($sheet, $currRow, $curDate, $allDayPeriods, $dayJournals, $assignment);
            }
        } else {
            $currRow = $this->renderSpreadsheetDayRows($sheet, $currRow, $referenceDate, $allDayPeriods, $journals, $assignment);
        }

        // Table Borders & Styles
        $lastRow = max(10, $currRow - 1);
        $tableRange = "A{$headerRow1}:J{$lastRow}";
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D5DD'],
                ],
            ],
        ]);

        $headerRange = "A{$headerRow1}:J{$headerRow2}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D47A1'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle("F{$headerRow2}:I{$headerRow2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1565C0');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(8);
        $sheet->getColumnDimension('J')->setWidth(38);

        // Signatures (2 rows below table)
        $sigRow = $lastRow + 2;
        $sheet->setCellValue("B{$sigRow}", 'Mengetahui,');
        $sheet->setCellValue('B'.($sigRow + 1), 'Kepala Sekolah / Waka Kurikulum');
        $sheet->setCellValue("H{$sigRow}", 'Karanganyar, '.now()->locale('id')->isoFormat('D MMMM Y'));
        $sheet->setCellValue('H'.($sigRow + 1), 'Guru Mata Pelajaran');

        $sigNameRow = $sigRow + 4;
        $sheet->setCellValue("B{$sigNameRow}", '( .................................................... )');
        $sheet->setCellValue("H{$sigNameRow}", '( '.(Auth::user()->name).' )');
        $sheet->getStyle("B{$sigRow}:H{$sigNameRow}")->getFont()->setSize(10);
        $sheet->getStyle("B{$sigNameRow}")->getFont()->setBold(true);
        $sheet->getStyle("H{$sigNameRow}")->getFont()->setBold(true);

        $cleanClass = preg_replace('/[^A-Za-z0-9_-]/', '_', $className);
        $suffix = $range === 'weekly' ? 'Mingguan_Mg'.$weekStart->isoWeek() : $selectedDate;
        $fileName = 'Jurnal_Kelas_'.$cleanClass.'_'.$suffix.'_'.date('His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function renderSpreadsheetDayRows($sheet, int $startRow, Carbon $date, $allDayPeriods, $journals, $assignment): int
    {
        $dayName = $date->isoFormat('dddd');
        $dateFormatted = $date->format('d/m/Y');
        $currRow = $startRow;
        $coveredPeriodNumbers = [];

        foreach ($allDayPeriods as $period) {
            if ($period->is_break) {
                // Break row merged across A:J
                $sheet->setCellValue("A{$currRow}", strtoupper($period->name).' ('.substr($period->start_time, 0, 5).' - '.substr($period->end_time, 0, 5).')');
                $sheet->mergeCells("A{$currRow}:J{$currRow}");
                $sheet->getStyle("A{$currRow}:J{$currRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '78350F'], 'size' => 9],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'], // light amber
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($currRow)->setRowHeight(20);
                $currRow++;

                continue;
            }

            $pNum = $period->period_number;
            if (in_array($pNum, $coveredPeriodNumbers)) {
                continue;
            }

            $j = $journals->first(function ($item) use ($pNum, $period) {
                $startNum = $item->startPeriod?->period_number ?? $item->start_period_id;

                return $startNum == $pNum || $item->start_period_id == $period->id;
            });

            if ($j) {
                $startNum = $j->startPeriod?->period_number ?? $pNum;
                $endNum = $j->endPeriod?->period_number ?? $startNum;
                for ($k = min($startNum, $endNum); $k <= max($startNum, $endNum); $k++) {
                    $coveredPeriodNumbers[] = $k;
                }

                $sheet->setCellValue("A{$currRow}", "{$dayName}\n{$dateFormatted}");
                $periodLabel = ($startNum == $endNum) ? "Jam {$startNum}" : "Jam {$startNum} - {$endNum}";
                $timeLabel = substr($j->startPeriod?->start_time ?? $period->start_time, 0, 5).' - '.substr($j->endPeriod?->end_time ?? $period->end_time, 0, 5);
                $sheet->setCellValue("B{$currRow}", "{$periodLabel}\n{$timeLabel}");
                $sheet->setCellValue("C{$currRow}", $j->teachingAssignment?->subject?->name ?? $assignment->subject?->name);
                $sheet->setCellValue("D{$currRow}", $j->creator?->user?->name ?? 'Guru');
                $sheet->setCellValue("E{$currRow}", $j->material);
                $sheet->setCellValue("F{$currRow}", $j->hadir_count);
                $sheet->setCellValue("G{$currRow}", $j->sakit_count);
                $sheet->setCellValue("H{$currRow}", $j->izin_count);
                $sheet->setCellValue("I{$currRow}", $j->alpha_count);

                // Absence notes
                $absentDetails = [];
                foreach ($j->attendances as $att) {
                    if ($att->status !== AttendanceStatus::Present) {
                        $stName = $att->student?->full_name ?? 'Siswa';
                        $stStatus = match ($att->status) {
                            AttendanceStatus::Sick => 'Sakit',
                            AttendanceStatus::Permit => 'Izin',
                            AttendanceStatus::Absent => 'Alpha',
                            default => 'Hadir',
                        };
                        $noteText = $att->note ? " ({$att->note})" : '';
                        $absentDetails[] = "{$stName} [{$stStatus}]{$noteText}";
                    }
                }
                $extraNote = trim(preg_replace('/Hadir:\s*\d+\s*\|\s*Sakit:\s*\d+\s*\|\s*Izin:\s*\d+\s*\|\s*Alpha:\s*\d+/i', '', $j->notes ?? ''));
                if ($extraNote) {
                    $absentDetails[] = "Catatan: {$extraNote}";
                }

                $sheet->setCellValue("J{$currRow}", ! empty($absentDetails) ? implode("\n", $absentDetails) : 'Semua Hadir');

                $sheet->getStyle("A{$currRow}:J{$currRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("A{$currRow}:B{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$currRow}:I{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($currRow)->setRowHeight(28);
                $currRow++;
            } else {
                // Empty placeholder row
                $sheet->setCellValue("A{$currRow}", "{$dayName}\n{$dateFormatted}");
                $timeLabel = substr($period->start_time, 0, 5).' - '.substr($period->end_time, 0, 5);
                $sheet->setCellValue("B{$currRow}", "Jam {$pNum}\n{$timeLabel}");
                $sheet->setCellValue("C{$currRow}", '-');
                $sheet->setCellValue("D{$currRow}", '-');
                $sheet->setCellValue("E{$currRow}", 'Belum diisi');
                $sheet->setCellValue("F{$currRow}", '-');
                $sheet->setCellValue("G{$currRow}", '-');
                $sheet->setCellValue("H{$currRow}", '-');
                $sheet->setCellValue("I{$currRow}", '-');
                $sheet->setCellValue("J{$currRow}", '-');

                $sheet->getStyle("A{$currRow}:J{$currRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("A{$currRow}:B{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$currRow}:J{$currRow}")->getFont()->setColor(new Color('94A3B8'));
                $sheet->getStyle("F{$currRow}:I{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($currRow)->setRowHeight(22);
                $currRow++;
            }
        }

        return $currRow;
    }

    private function resolveExportData(Request $request): array
    {
        $teacher = Auth::user()->teacherProfile;
        $assignmentId = $request->query('assignment_id');

        $assignment = null;
        if ($assignmentId) {
            $assignment = TeachingAssignment::with([
                'schoolClass.gradeLevel',
                'schoolClass.department',
                'schoolClass.homeroomTeacher.user',
                'subject',
                'semester.academicYear',
                'teacher.user',
            ])
                ->where('id', $assignmentId)
                ->first();
        }

        if (! $assignment && $teacher) {
            $assignment = TeachingAssignment::with([
                'schoolClass.gradeLevel',
                'schoolClass.department',
                'schoolClass.homeroomTeacher.user',
                'subject',
                'semester.academicYear',
                'teacher.user',
            ])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $assignment) {
            abort(404, 'Data penugasan kelas tidak ditemukan.');
        }

        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $range = $request->query('range', 'daily');

        $referenceDate = Carbon::parse($selectedDate)->locale('id');
        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);

        $allDayPeriods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();

        $journalQuery = ClassJournal::with([
            'startPeriod',
            'endPeriod',
            'attendances.student',
            'creator.user',
            'teachingAssignment.subject',
        ])
            ->whereHas('teachingAssignment', function ($q) use ($assignment) {
                $q->where('class_id', $assignment->class_id);
            });

        if ($range === 'weekly') {
            $journalQuery->whereBetween('journal_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->orderBy('journal_date')
                ->orderBy('start_period_id');
        } else {
            $journalQuery->whereDate('journal_date', $selectedDate)
                ->orderBy('start_period_id');
        }

        $journals = $journalQuery->get()->values();

        $enrolledStudents = ClassEnrollment::with('student')
            ->where('class_id', $assignment->class_id)
            ->where('status', 'ACTIVE')
            ->get()
            ->pluck('student')
            ->filter()
            ->sortBy('full_name')
            ->values();

        $schoolProfile = SchoolProfile::first();

        return [
            'assignment' => $assignment,
            'selectedDate' => $selectedDate,
            'range' => $range,
            'referenceDate' => $referenceDate,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'allDayPeriods' => $allDayPeriods,
            'journals' => $journals,
            'enrolledStudents' => $enrolledStudents,
            'schoolProfile' => $schoolProfile,
        ];
    }
}
