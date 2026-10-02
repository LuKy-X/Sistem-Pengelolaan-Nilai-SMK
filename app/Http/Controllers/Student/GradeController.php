<?php

namespace App\Http\Controllers\Student;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Gradebook;
use App\Models\GradebookScore;
use App\Models\StudentGradeNote;
use App\Services\StudentAcademicSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(
        private readonly StudentAcademicSummaryService $summary,
    ) {}

    public function index(): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        // Buku nilai tempat siswa terdaftar sebagai anggota (snapshot membership).
        $memberships = $this->summary->activeGradebooks($student);
        $summaries = $this->summary->gradebookSummaries($student, $memberships);

        return view('student.grades.index', compact('student', 'memberships', 'summaries'));
    }

    /**
     * Rekap nilai lintas mata pelajaran: rata-rata keseluruhan, predikat,
     * dan rincian per buku nilai yang diikuti siswa.
     */
    public function recap(): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $academic = $this->summary->forStudent($student);

        return view('student.grades.recap', [
            'student' => $student,
            'subjects' => $academic['subjects'],
            'overall' => $academic['overall'],
            'tasks' => $academic['tasks'],
            'passingScore' => StudentAcademicSummaryService::PASSING_SCORE,
        ]);
    }

    public function show(Gradebook $gradebook): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        // Siswa hanya boleh membuka buku nilai tempat ia terdaftar sebagai anggota.
        $isMember = $student->gradebookMemberships()
            ->where('gradebook_id', $gradebook->id)
            ->exists();

        abort_unless($isMember, 403, 'Anda tidak terdaftar pada buku nilai ini.');

        $gradebook->load([
            'teachingAssignment.subject',
            'teachingAssignment.schoolClass',
            'teachingAssignment.semester.academicYear',
            'teachingAssignment.teacher',
            'columns' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order'),
            'columns.category',
            'columns.sourceColumns',
        ]);

        $scores = GradebookScore::query()
            ->where('student_id', $student->id)
            ->whereIn('gradebook_column_id', $gradebook->columns->pluck('id'))
            ->with('rubricScores.criterion')
            ->get()
            ->keyBy('gradebook_column_id');

        $values = [];
        foreach ($gradebook->columns as $column) {
            $values[$column->id] = $this->summary->resolveColumnValue($column, $scores);
        }

        // Tugas terbit yang tertaut ke kolom buku nilai ini, beserta pengumpulan siswa.
        $assessmentsByColumn = Assessment::query()
            ->where('teaching_assignment_id', $gradebook->teaching_assignment_id)
            ->where('status', AssessmentStatus::Published->value)
            ->whereIn('gradebook_column_id', $gradebook->columns->pluck('id'))
            ->with(['submissions' => fn ($query) => $query->where('student_id', $student->id)])
            ->get()
            ->groupBy('gradebook_column_id');

        // Catatan evaluasi / remedial dari guru untuk siswa ini pada mapel ini.
        $notes = StudentGradeNote::query()
            ->where('teaching_assignment_id', $gradebook->teaching_assignment_id)
            ->where('student_id', $student->id)
            ->with('teacher')
            ->latest()
            ->get();

        $summary = $this->summary->gradebookSummaries($student, collect([$gradebook]))[$gradebook->id];

        return view('student.grades.show', compact(
            'student',
            'gradebook',
            'scores',
            'values',
            'assessmentsByColumn',
            'notes',
            'summary',
        ));
    }
}
