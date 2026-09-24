<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\TeachingAssignment;
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

        $journals = collect();
        $enrolledStudents = collect();
        $lessonPeriods = LessonPeriod::orderBy('period_number')->get();

        if ($selectedAssignment) {
            $journals = ClassJournal::with([
                'startPeriod',
                'endPeriod',
                'attendances.student',
                'creator',
            ])
                ->where('teaching_assignment_id', $selectedAssignment->id)
                ->latest('journal_date')
                ->get();

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedAssignment->class_id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name');
        }

        return view('teacher.journals.index', compact(
            'assignments',
            'selectedAssignment',
            'journals',
            'enrolledStudents',
            'lessonPeriods'
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
                foreach ($validated['absences'] as $abs) {
                    if (empty($abs['student_id'])) {
                        continue;
                    }

                    $statusEnum = match (strtoupper($abs['status'])) {
                        'SAKIT', 'S' => AttendanceStatus::Sick,
                        'IZIN', 'I' => AttendanceStatus::Permitted,
                        'ALPHA', 'A' => AttendanceStatus::Absent,
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
        ])->with('success', 'Jurnal kelas dan absensi berhasil disimpan.');
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
