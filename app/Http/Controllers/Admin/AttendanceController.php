<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\Department;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $classId = $request->query('class_id') ? (int) $request->query('class_id') : null;
        $departmentId = $request->query('department_id') ? (int) $request->query('department_id') : null;
        $teacherId = $request->query('teacher_id') ? (int) $request->query('teacher_id') : null;
        $subjectId = $request->query('subject_id') ? (int) $request->query('subject_id') : null;
        $journalStatus = $request->query('journal_status'); // 'all', 'filled', 'unfilled'
        $layout = $request->query('layout', 'schedule'); // 'schedule' or 'table'
        $tableTab = $request->query('table_tab', 'sessions'); // 'sessions' or 'students'

        $referenceDate = Carbon::parse($date);
        $dayOfWeek = $referenceDate->dayOfWeekIso; // 1 = Senin, 7 = Minggu
        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);
        $todayStr = now()->format('Y-m-d');
        $isToday = ($date === $todayStr);
        $isPast = ($date < $todayStr);
        $isFuture = ($date > $todayStr);
        $isCurrentWeek = now()->betweenIncluded($weekStart, $weekEnd);
        $prevWeekDate = $weekStart->copy()->subWeek()->format('Y-m-d');
        $nextWeekDate = $weekStart->copy()->addWeek()->format('Y-m-d');
        $prevDate = $referenceDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $referenceDate->copy()->addDay()->format('Y-m-d');

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $dayName = $dayNames[$dayOfWeek] ?? 'Hari '.$dayOfWeek;

        // Master Filter Lists
        $classesQuery = SchoolClass::where('is_active', true)
            ->with(['department', 'homeroomTeacher.user', 'enrollments.student'])
            ->orderBy('name');
        if ($departmentId) {
            $classesQuery->where('department_id', $departmentId);
        }
        $classes = $classesQuery->get();

        $departments = Department::orderBy('name')->get();
        $teachers = TeacherProfile::with('user')->orderBy('full_name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $allDayPeriods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();
        $lessonPeriods = LessonPeriod::where('is_break', false)->orderBy('period_number')->get();

        // 1. Fetch Today's Teaching Schedules for active assignments
        $schedulesQuery = TeachingSchedule::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.teacher.user',
            'startPeriod',
            'endPeriod',
        ])
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('teachingAssignment', function ($q) use ($classId, $departmentId, $teacherId, $subjectId) {
                $q->where('is_active', true);
                if ($classId) {
                    $q->where('class_id', $classId);
                }
                if ($departmentId) {
                    $q->whereHas('schoolClass', fn ($sc) => $sc->where('department_id', $departmentId));
                }
                if ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                }
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            });
        $todaySchedules = $schedulesQuery->get()->sortBy(fn ($s) => $s->startPeriod?->period_number ?? 0)->values();

        // 2. Fetch Class Journals for this date
        $journalsQuery = ClassJournal::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.teacher.user',
            'teachingAssignment.schoolClass.enrollments.student',
            'startPeriod',
            'endPeriod',
            'attendances.student',
            'creator.user',
        ])
            ->whereDate('journal_date', $date)
            ->whereHas('teachingAssignment', function ($q) use ($classId, $departmentId, $teacherId, $subjectId) {
                if ($classId) {
                    $q->where('class_id', $classId);
                }
                if ($departmentId) {
                    $q->whereHas('schoolClass', fn ($sc) => $sc->where('department_id', $departmentId));
                }
                if ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                }
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            });

        if ($teacherId) {
            $journalsQuery->orWhere(function ($oq) use ($date, $teacherId, $classId, $departmentId, $subjectId) {
                $oq->whereDate('journal_date', $date)->where('created_by', $teacherId);
                if ($classId) {
                    $oq->whereHas('teachingAssignment', fn ($ta) => $ta->where('class_id', $classId));
                }
                if ($departmentId) {
                    $oq->whereHas('teachingAssignment.schoolClass', fn ($sc) => $sc->where('department_id', $departmentId));
                }
                if ($subjectId) {
                    $oq->whereHas('teachingAssignment', fn ($ta) => $ta->where('subject_id', $subjectId));
                }
            });
        }

        $todayJournals = $journalsQuery->get()->sortBy(fn ($j) => $j->startPeriod?->period_number ?? 0)->values();

        // 3. Compute 5 KPI Cards Statistics
        $totalSchedules = $todaySchedules->count();
        $filledJournalsCount = $todayJournals->count();
        $unfilledCount = max(0, $totalSchedules - $filledJournalsCount);

        $totalHadir = $todayJournals->sum(fn ($j) => $j->hadir_count);
        $totalSakit = $todayJournals->sum(fn ($j) => $j->sakit_count);
        $totalIzin = $todayJournals->sum(fn ($j) => $j->izin_count);
        $totalAlpha = $todayJournals->sum(fn ($j) => $j->alpha_count);
        $totalAbsence = $totalSakit + $totalIzin + $totalAlpha;
        $totalStudentsLogged = $totalHadir + $totalAbsence;

        $completionRate = $totalSchedules > 0
            ? round((min($filledJournalsCount, $totalSchedules) / $totalSchedules) * 100, 1)
            : ($filledJournalsCount > 0 ? 100.0 : 0.0);

        $attendanceRate = $totalStudentsLogged > 0
            ? round(($totalHadir / $totalStudentsLogged) * 100, 1)
            : 100.0;

        $stats = [
            'total_schedules' => $totalSchedules,
            'filled_journals' => $filledJournalsCount,
            'unfilled_journals' => $unfilledCount,
            'completion_rate' => $completionRate,
            'total_hadir' => $totalHadir,
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_alpha' => $totalAlpha,
            'total_absence' => $totalAbsence,
            'total_students' => $totalStudentsLogged,
            'attendance_rate' => $attendanceRate,
        ];

        // 4. Prepare Data for "Semua Kelas" Grid (Schedule Layout)
        $classesGrid = collect();
        foreach ($classes as $cls) {
            $clsSchedules = $todaySchedules->filter(fn ($s) => $s->teachingAssignment?->class_id === $cls->id)->values();
            $clsJournals = $todayJournals->filter(fn ($j) => $j->teachingAssignment?->class_id === $cls->id)->values();
            $clsStudentCount = $cls->enrollments->where('status', 'ACTIVE')->count();
            if ($clsStudentCount === 0) {
                $clsStudentCount = $cls->enrollments->count();
            }

            $schedCount = $clsSchedules->count();
            $filledCount = $clsJournals->count();

            if ($schedCount === 0 && $filledCount === 0) {
                $statusCode = 'no_schedule';
                $statusLabel = 'Tidak Ada Jadwal Hari Ini';
                $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
            } elseif ($filledCount >= $schedCount && $schedCount > 0) {
                $statusCode = 'completed';
                $statusLabel = "Selesai ({$filledCount}/{$schedCount} Sesi)";
                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 font-semibold';
            } elseif ($filledCount > 0) {
                $statusCode = 'partial';
                $statusLabel = "Sebagian ({$filledCount}/{$schedCount} Sesi)";
                $badgeClass = 'bg-amber-50 text-amber-800 border-amber-300 font-semibold';
            } else {
                if ($isPast) {
                    $statusCode = 'overdue';
                    $statusLabel = "Terlewat: {$schedCount} Sesi Belum Diisi";
                    $badgeClass = 'bg-rose-50 text-rose-800 border-rose-300 font-semibold';
                } elseif ($isToday) {
                    $statusCode = 'today_unfilled';
                    $statusLabel = "Hari Ini: {$schedCount} Sesi Belum Diisi";
                    $badgeClass = 'bg-blue-50 text-blue-800 border-blue-300 font-semibold';
                } else {
                    $statusCode = 'upcoming';
                    $statusLabel = "{$schedCount} Sesi Terjadwal";
                    $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200 font-semibold';
                }
            }

            // Presence summary for this class
            $clsHadir = $clsJournals->sum(fn ($j) => $j->hadir_count);
            $clsSakit = $clsJournals->sum(fn ($j) => $j->sakit_count);
            $clsIzin = $clsJournals->sum(fn ($j) => $j->izin_count);
            $clsAlpha = $clsJournals->sum(fn ($j) => $j->alpha_count);

            // Today's subjects list for preview
            $schedItems = $clsSchedules->map(function ($s) use ($clsJournals) {
                $isFilled = $clsJournals->first(function ($j) use ($s) {
                    if ($j->schedule_id && $j->schedule_id === $s->id) {
                        return true;
                    }

                    return $j->teaching_assignment_id === $s->teaching_assignment_id
                        && $j->start_period_id == $s->start_period_id;
                }) !== null;

                $startNum = $s->startPeriod?->period_number;
                $endNum = $s->endPeriod?->period_number ?? $startNum;
                $pLabel = $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum}-{$endNum}";

                return [
                    'period_label' => $pLabel,
                    'subject' => $s->teachingAssignment?->subject?->name ?? 'Mapel',
                    'teacher' => $s->teachingAssignment?->teacher?->full_name ?? ($s->teachingAssignment?->teacher?->user?->name ?? 'Guru'),
                    'is_filled' => $isFilled,
                ];
            });

            $classesGrid->push([
                'class' => $cls,
                'status_code' => $statusCode,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'student_count' => $clsStudentCount,
                'schedules_count' => $schedCount,
                'filled_count' => $filledCount,
                'hadir' => $clsHadir,
                'sakit' => $clsSakit,
                'izin' => $clsIzin,
                'alpha' => $clsAlpha,
                'sched_items' => $schedItems,
            ]);
        }

        // 5. Prepare Data for "Per Kelas" Single Class View
        $selectedClass = $classId ? $classes->firstWhere('id', $classId) : null;
        $classJournals = collect();
        $classSchedules = collect();
        $enrolledStudents = collect();

        if ($selectedClass) {
            $classJournals = $todayJournals->filter(fn ($j) => $j->teachingAssignment?->class_id === $selectedClass->id)->values();
            $classSchedules = $todaySchedules->filter(fn ($s) => $s->teachingAssignment?->class_id === $selectedClass->id)->values();

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedClass->id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name')
                ->values();
        }

        // 6. Prepare Data for "Tampilan Tabel" (Tabular Sesi Mengajar)
        $tableSessions = collect();

        // Add filled journals
        foreach ($todayJournals as $j) {
            $startNum = $j->startPeriod?->period_number;
            $endNum = $j->endPeriod?->period_number ?? $startNum;
            $timeRange = ($j->startPeriod && $j->endPeriod)
                ? substr($j->startPeriod->start_time, 0, 5).' - '.substr($j->endPeriod->end_time, 0, 5)
                : '';
            $periodLabel = $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum} - {$endNum}";

            $ta = $j->teachingAssignment;
            $cls = $ta?->schoolClass;
            $subj = $ta?->subject;
            $teacher = $j->creator?->user?->name ?? ($ta?->teacher?->full_name ?? ($ta?->teacher?->user?->name ?? 'Guru'));

            // Build absent students array
            $absentList = $j->attendances
                ->filter(fn ($att) => $att->status !== AttendanceStatus::Present)
                ->map(fn ($att) => [
                    'student_id' => $att->student_id,
                    'name' => $att->student?->full_name ?? 'Siswa #'.$att->student_id,
                    'nis' => $att->student?->nis ?? '-',
                    'status' => $att->status instanceof AttendanceStatus ? $att->status->value : (string) $att->status,
                    'note' => $att->note ?? '',
                ])->values();

            // Build full class students attendance list for the read-only inspection modal
            $clsStudents = $cls?->enrollments?->where('status', 'ACTIVE')->pluck('student')->filter() ?? collect();
            if ($clsStudents->isEmpty() && $cls?->enrollments) {
                $clsStudents = $cls->enrollments->pluck('student')->filter();
            }
            $attStudents = $j->attendances->pluck('student')->filter();
            $mergedStudents = $clsStudents->concat($attStudents)->unique('id')->sortBy('full_name')->values();

            $attendanceMap = $j->attendances->keyBy('student_id');

            $allStudentsDetail = $mergedStudents->map(function ($st) use ($attendanceMap) {
                $att = $attendanceMap->get($st->id);
                $statusVal = $att ? ($att->status instanceof AttendanceStatus ? $att->status->value : (string) $att->status) : 'PRESENT';

                return [
                    'id' => $st->id,
                    'nis' => $st->nis ?? '-',
                    'name' => $st->full_name,
                    'status' => $statusVal,
                    'note' => $att?->note ?? '',
                ];
            })->values();

            $tableSessions->push([
                'type' => 'filled',
                'journal_id' => $j->id,
                'period_label' => $periodLabel,
                'time_range' => $timeRange,
                'start_period_number' => $startNum ?? 1,
                'class_id' => $cls?->id,
                'class_name' => $cls?->name ?? 'Kelas',
                'department_name' => $cls?->department?->name ?? '-',
                'subject_name' => $subj?->name ?? 'Mata Pelajaran',
                'teacher_name' => $teacher,
                'material' => $j->material,
                'notes' => $j->notes,
                'hadir_count' => $j->hadir_count,
                'sakit_count' => $j->sakit_count,
                'izin_count' => $j->izin_count,
                'alpha_count' => $j->alpha_count,
                'absent_students' => $absentList,
                'all_students' => $allStudentsDetail,
                'status_code' => 'filled',
                'status_label' => 'Sudah Diisi',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ]);
        }

        // Add unfilled scheduled sessions
        foreach ($todaySchedules as $sched) {
            $isFilled = $todayJournals->first(function ($j) use ($sched) {
                if ($j->schedule_id && $j->schedule_id === $sched->id) {
                    return true;
                }

                return $j->teaching_assignment_id === $sched->teaching_assignment_id
                    && $j->start_period_id == $sched->start_period_id;
            }) !== null;

            if (! $isFilled) {
                $startNum = $sched->startPeriod?->period_number;
                $endNum = $sched->endPeriod?->period_number ?? $startNum;
                $timeRange = ($sched->startPeriod && $sched->endPeriod)
                    ? substr($sched->startPeriod->start_time, 0, 5).' - '.substr($sched->endPeriod->end_time, 0, 5)
                    : '';
                $periodLabel = $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum} - {$endNum}";

                $ta = $sched->teachingAssignment;
                $cls = $ta?->schoolClass;
                $subj = $ta?->subject;
                $teacher = $ta?->teacher?->full_name ?? ($ta?->teacher?->user?->name ?? 'Guru');

                $statusLabel = $isPast ? 'Terlewat (Belum Diisi)' : ($isToday ? 'Hari Ini (Belum Diisi)' : 'Belum Mulai');
                $badgeClass = $isPast ? 'bg-rose-50 text-rose-700 border-rose-200 font-semibold' : ($isToday ? 'bg-amber-50 text-amber-700 border-amber-200 font-semibold' : 'bg-slate-100 text-slate-600 border-slate-200');

                $tableSessions->push([
                    'type' => 'unfilled',
                    'journal_id' => null,
                    'period_label' => $periodLabel,
                    'time_range' => $timeRange,
                    'start_period_number' => $startNum ?? 99,
                    'class_id' => $cls?->id,
                    'class_name' => $cls?->name ?? 'Kelas',
                    'department_name' => $cls?->department?->name ?? '-',
                    'subject_name' => $subj?->name ?? 'Mata Pelajaran',
                    'teacher_name' => $teacher,
                    'material' => 'Belum ada materi (Jurnal belum diisi guru)',
                    'notes' => null,
                    'hadir_count' => 0,
                    'sakit_count' => 0,
                    'izin_count' => 0,
                    'alpha_count' => 0,
                    'absent_students' => collect(),
                    'all_students' => collect(),
                    'status_code' => $isPast ? 'overdue' : 'unfilled',
                    'status_label' => $statusLabel,
                    'badge_class' => $badgeClass,
                ]);
            }
        }

        // Apply journal status filter on table sessions if requested
        if ($journalStatus === 'filled') {
            $tableSessions = $tableSessions->where('type', 'filled');
        } elseif ($journalStatus === 'unfilled') {
            $tableSessions = $tableSessions->where('type', 'unfilled');
        }

        // Sort table sessions: class name, then start period number
        $tableSessions = $tableSessions->sortBy([
            ['class_name', 'asc'],
            ['start_period_number', 'asc'],
        ])->values();

        // 7. Data for Flat Student Attendances Tab (if table_tab === 'students')
        $studentAttendancesQuery = JournalAttendance::with([
            'student',
            'journal.teachingAssignment.schoolClass',
            'journal.teachingAssignment.subject',
            'journal.teachingAssignment.teacher.user',
        ])
            ->whereHas('journal', function ($q) use ($date, $classId, $departmentId, $teacherId, $subjectId) {
                $q->whereDate('journal_date', $date);
                if ($classId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('class_id', $classId));
                }
                if ($departmentId) {
                    $q->whereHas('teachingAssignment.schoolClass', fn ($sc) => $sc->where('department_id', $departmentId));
                }
                if ($teacherId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('teacher_id', $teacherId));
                }
                if ($subjectId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('subject_id', $subjectId));
                }
            });

        $studentAttendances = $studentAttendancesQuery->latest()->paginate(25)->withQueryString();

        return view('admin.attendance.index', compact(
            'date',
            'classId',
            'departmentId',
            'teacherId',
            'subjectId',
            'journalStatus',
            'layout',
            'tableTab',
            'referenceDate',
            'dayOfWeek',
            'dayName',
            'weekStart',
            'weekEnd',
            'todayStr',
            'isToday',
            'isPast',
            'isFuture',
            'isCurrentWeek',
            'prevWeekDate',
            'nextWeekDate',
            'prevDate',
            'nextDate',
            'classes',
            'departments',
            'teachers',
            'subjects',
            'allDayPeriods',
            'lessonPeriods',
            'todaySchedules',
            'todayJournals',
            'stats',
            'classesGrid',
            'selectedClass',
            'classJournals',
            'classSchedules',
            'enrolledStudents',
            'tableSessions',
            'studentAttendances'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $request->query('date', today()->toDateString());
        $classId = $request->query('class_id');
        $format = $request->query('format', 'csv'); // 'csv' or 'excel'
        $type = $request->query('type', 'sessions'); // 'sessions' or 'students'

        if ($format === 'excel' && class_exists(Spreadsheet::class)) {
            return $this->exportExcel($date, $classId, $type);
        }

        return $this->exportCsv($date, $classId, $type);
    }

    protected function exportCsv(string $date, ?string $classId, string $type): StreamedResponse
    {
        $fileName = ($type === 'sessions' ? 'rekap_jurnal_sesi_' : 'rekap_absensi_siswa_').$date.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        if ($type === 'sessions') {
            $journals = ClassJournal::with([
                'teachingAssignment.schoolClass',
                'teachingAssignment.subject',
                'teachingAssignment.teacher.user',
                'startPeriod',
                'endPeriod',
            ])
                ->whereDate('journal_date', $date)
                ->when($classId, function ($q) use ($classId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('class_id', $classId));
                })
                ->get()
                ->sortBy(fn ($j) => $j->startPeriod?->period_number ?? 0);

            return response()->stream(function () use ($journals, $date) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($file, ['No', 'Tanggal', 'Kelas', 'Jam Pelajaran', 'Mata Pelajaran', 'Guru Pengajar', 'Materi', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Catatan']);

                foreach ($journals as $index => $row) {
                    $startNum = $row->startPeriod?->period_number;
                    $endNum = $row->endPeriod?->period_number ?? $startNum;
                    $pLabel = $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum} - {$endNum}";

                    fputcsv($file, [
                        $index + 1,
                        $date,
                        $row->teachingAssignment?->schoolClass?->name ?? '-',
                        $pLabel,
                        $row->teachingAssignment?->subject?->name ?? '-',
                        $row->creator?->user?->name ?? ($row->teachingAssignment?->teacher?->full_name ?? '-'),
                        $row->material ?? '-',
                        $row->hadir_count,
                        $row->sakit_count,
                        $row->izin_count,
                        $row->alpha_count,
                        $row->notes ?? '-',
                    ]);
                }

                fclose($file);
            }, 200, $headers);
        }

        // Student-level export
        $attendances = JournalAttendance::with([
            'student',
            'journal.teachingAssignment.schoolClass',
            'journal.teachingAssignment.subject',
            'journal.teachingAssignment.teacher',
        ])
            ->whereHas('journal', function ($q) use ($date, $classId) {
                $q->whereDate('journal_date', $date);
                if ($classId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('class_id', $classId));
                }
            })
            ->get();

        return response()->stream(function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No', 'Tanggal', 'Kelas', 'NIS', 'Nama Siswa', 'Status', 'Mata Pelajaran', 'Guru', 'Catatan']);

            foreach ($attendances as $index => $row) {
                $statusVal = $row->status instanceof AttendanceStatus ? $row->status->value : $row->status;
                fputcsv($file, [
                    $index + 1,
                    $row->journal?->journal_date?->format('Y-m-d') ?? '-',
                    $row->journal?->teachingAssignment?->schoolClass?->name ?? '-',
                    $row->student?->nis ?? '-',
                    $row->student?->full_name ?? '-',
                    $statusVal,
                    $row->journal?->teachingAssignment?->subject?->name ?? '-',
                    $row->journal?->teachingAssignment?->teacher?->full_name ?? '-',
                    $row->note ?? '-',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    protected function exportExcel(string $date, ?string $classId, string $type): StreamedResponse
    {
        $fileName = ($type === 'sessions' ? 'rekap_jurnal_sesi_' : 'rekap_absensi_siswa_').$date.'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($type === 'sessions' ? 'Rekap Jurnal Sesi' : 'Rekap Absensi Siswa');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Header Title
        $sheet->setCellValue('A1', 'REKAPITULASI JURNAL PEMBELAJARAN & PRESENSI SISWA');
        $sheet->setCellValue('A2', 'SMK NEGERI 2 KARANGANYAR');
        $sheet->setCellValue('A3', 'Tanggal: '.Carbon::parse($date)->translatedFormat('l, d F Y'));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        if ($type === 'sessions') {
            $journals = ClassJournal::with([
                'teachingAssignment.schoolClass',
                'teachingAssignment.subject',
                'teachingAssignment.teacher.user',
                'startPeriod',
                'endPeriod',
            ])
                ->whereDate('journal_date', $date)
                ->when($classId, function ($q) use ($classId) {
                    $q->whereHas('teachingAssignment', fn ($ta) => $ta->where('class_id', $classId));
                })
                ->get()
                ->sortBy(fn ($j) => $j->startPeriod?->period_number ?? 0);

            $tableHeaders = ['No', 'Kelas', 'Jam Pelajaran', 'Mata Pelajaran', 'Guru Pengajar', 'Materi Pembelajaran', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Catatan Guru'];
            $sheet->fromArray($tableHeaders, null, 'A5');
            $sheet->getStyle('A5:K5')->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
            $sheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0D47A1');
            $sheet->getStyle('A5:K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowIdx = 6;
            foreach ($journals as $idx => $j) {
                $startNum = $j->startPeriod?->period_number;
                $endNum = $j->endPeriod?->period_number ?? $startNum;
                $pLabel = $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum} - {$endNum}";

                $sheet->setCellValue("A{$rowIdx}", $idx + 1);
                $sheet->setCellValue("B{$rowIdx}", $j->teachingAssignment?->schoolClass?->name ?? '-');
                $sheet->setCellValue("C{$rowIdx}", $pLabel);
                $sheet->setCellValue("D{$rowIdx}", $j->teachingAssignment?->subject?->name ?? '-');
                $sheet->setCellValue("E{$rowIdx}", $j->creator?->user?->name ?? ($j->teachingAssignment?->teacher?->full_name ?? '-'));
                $sheet->setCellValue("F{$rowIdx}", $j->material ?? '-');
                $sheet->setCellValue("G{$rowIdx}", $j->hadir_count);
                $sheet->setCellValue("H{$rowIdx}", $j->sakit_count);
                $sheet->setCellValue("I{$rowIdx}", $j->izin_count);
                $sheet->setCellValue("J{$rowIdx}", $j->alpha_count);
                $sheet->setCellValue("K{$rowIdx}", $j->notes ?? '-');

                $sheet->getStyle("A{$rowIdx}:C{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$rowIdx}:J{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $rowIdx++;
            }

            $lastRow = max(6, $rowIdx - 1);
            $sheet->getStyle("A5:K{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
