<?php

namespace App\Http\Controllers\Student;

use App\Enums\AssessmentStatus;
use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\StudentGradeNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        // Buku nilai tempat siswa terdaftar sebagai anggota (snapshot membership).
        $memberships = $student->gradebookMemberships()
            ->with([
                'gradebook.teachingAssignment.subject',
                'gradebook.teachingAssignment.schoolClass',
                'gradebook.teachingAssignment.semester.academicYear',
                'gradebook.teachingAssignment.teacher',
                'gradebook.columns' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order'),
            ])
            ->whereHas('gradebook', fn ($query) => $query->where('is_active', true))
            ->get()
            ->map(fn ($membership) => $membership->gradebook)
            ->filter()
            ->values();

        $summaries = [];

        foreach ($memberships as $gradebook) {
            $summaries[$gradebook->id] = $this->gradebookSummary($gradebook, $student->id);
        }

        return view('student.grades.index', compact('student', 'memberships', 'summaries'));
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
            $values[$column->id] = $this->resolveColumnValue($column, $scores);
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

        $summary = $this->gradebookSummary($gradebook, $student->id);

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

    /**
     * Ringkasan nilai siswa pada satu buku nilai: rata-rata kolom yang dihitung,
     * jumlah kolom terisi, dan total kolom terlihat.
     *
     * @return array{average: ?float, graded: int, total: int}
     */
    private function gradebookSummary(Gradebook $gradebook, int $studentId): array
    {
        $gradebook->loadMissing([
            'columns' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order'),
            'columns.sourceColumns',
        ]);

        $columns = $gradebook->columns;

        $scores = GradebookScore::query()
            ->where('student_id', $studentId)
            ->whereIn('gradebook_column_id', $columns->pluck('id'))
            ->get()
            ->keyBy('gradebook_column_id');

        $values = [];
        $graded = 0;

        foreach ($columns as $column) {
            $value = $this->resolveColumnValue($column, $scores);
            $values[$column->id] = $value;

            if ($value !== null) {
                $graded++;
            }
        }

        $included = $columns->filter(fn (GradebookColumn $column) => $column->is_included_in_average);
        $includedValues = $included
            ->map(fn (GradebookColumn $column) => $values[$column->id])
            ->filter(fn ($value) => $value !== null);

        return [
            'average' => $includedValues->isNotEmpty() ? round($includedValues->avg(), 2) : null,
            'graded' => $graded,
            'total' => $columns->count(),
        ];
    }

    /**
     * Nilai satu kolom untuk siswa: kolom SCORE membaca gradebook_scores,
     * kolom SUMMARY dihitung deterministik dari kolom sumbernya.
     *
     * @param  Collection<int, GradebookScore>  $scores
     */
    private function resolveColumnValue(GradebookColumn $column, Collection $scores): ?float
    {
        if ($column->column_type === GradebookColumnType::Score) {
            $score = $scores->get($column->id);

            return $score?->final_score !== null ? (float) $score->final_score : null;
        }

        // Kolom SUMMARY: hitung dari sumber.
        $sources = $column->sourceColumns;

        if ($sources->isEmpty()) {
            return null;
        }

        $sourceValues = $sources
            ->map(fn (GradebookColumn $source) => $scores->get($source->id)?->final_score)
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($sourceValues->isEmpty()) {
            return null;
        }

        return match ($column->calculation_type) {
            GradebookCalculationType::Sum => round($sourceValues->sum(), 2),
            GradebookCalculationType::WeightedAverage => $this->weightedAverage($sources, $scores),
            default => round($sourceValues->avg(), 2),
        };
    }

    /**
     * @param  Collection<int, GradebookColumn>  $sources
     * @param  Collection<int, GradebookScore>  $scores
     */
    private function weightedAverage(Collection $sources, Collection $scores): ?float
    {
        $weightedTotal = 0.0;
        $weightTotal = 0.0;

        foreach ($sources as $source) {
            $value = $scores->get($source->id)?->final_score;

            if ($value === null) {
                continue;
            }

            $weight = (float) ($source->pivot->weight ?? 1);
            $weightedTotal += (float) $value * $weight;
            $weightTotal += $weight;
        }

        return $weightTotal > 0 ? round($weightedTotal / $weightTotal, 2) : null;
    }
}
