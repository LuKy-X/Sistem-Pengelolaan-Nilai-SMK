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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalController extends Controller
{
    use ResolvesCounselorClasses;

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
        $lessonPeriods = LessonPeriod::orderBy('sort_order')->orderBy('period_number')->get();
        $previousJournalAttendances = collect();
        $previousJournalInfo = null;

        if ($selectedClass) {
            // Journals for the selected class on the selected day
            $journals = ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
                ->whereHas('teachingAssignment', function ($q) use ($selectedClass) {
                    $q->where('class_id', $selectedClass->id);
                })
                ->whereDate('journal_date', $selectedDate)
                ->get()
                ->sortBy(fn ($j) => $j->startPeriod?->period_number ?? 0)
                ->values();

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedClass->id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name')
                ->values();

            // Find the previous journal entry for this class to allow copying attendance
            $latestJournal = $journals->last() ?? ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator.user',
                'teachingAssignment.subject',
            ])
                ->whereHas('teachingAssignment', function ($q) use ($selectedClass) {
                    $q->where('class_id', $selectedClass->id);
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
        }

        return view('bk.journals.index', compact(
            'counselorClasses',
            'selectedClass',
            'selectedDate',
            'journals',
            'enrolledStudents',
            'lessonPeriods',
            'previousJournalAttendances',
            'previousJournalInfo',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer'],
            'journal_date' => ['required', 'date'],
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
        ]);

        // Ensure BK only records journals for their own counseled classes
        $classIds = $this->counselorClassIds();
        $schoolClass = SchoolClass::findOrFail($validated['class_id']);

        if (! in_array($schoolClass->id, $classIds)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        // Find any active teaching assignment for this class to associate the journal
        $assignment = $schoolClass->teachingAssignments()
            ->where('is_active', true)
            ->first();

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

            // BK journals are recorded under the counselor's teacher profile if available,
            // otherwise we use the first assignment's teacher as creator.
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

        return redirect()->route('counselor.journals.index', [
            'class_id' => $validated['class_id'],
            'date' => $validated['journal_date'],
        ])->with('success', 'Jurnal kelas dan absensi berhasil disimpan.');
    }
}
