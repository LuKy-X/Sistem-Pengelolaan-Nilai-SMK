<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Menyusun ringkasan akademik siswa: nilai per mata pelajaran, rata-rata
 * lintas mata pelajaran, predikat, dan progres pengumpulan tugas.
 *
 * Dipakai bersama oleh dashboard siswa dan halaman Rekap Nilai agar keduanya
 * menampilkan angka yang sama persis.
 *
 * Bagian penting dari kelas ini adalah menghitung nilai kolom secara
 * deterministik dari baris yang tersimpan. Kolom bertipe SCORE dibaca dari
 * gradebook_scores, sedangkan kolom SUMMARY dihitung ulang dari kolom
 * sumbernya karena kolom SUMMARY tidak pernah disimpan ke database.
 *
 * Semua nilai diambil dengan satu kueri GradebookScore untuk seluruh buku nilai
 * siswa, bukan satu kueri per buku nilai.
 */
class StudentAcademicSummaryService
{
    /**
     * Kriteria ketuntasan minimal, dipakai untuk predikat dan penanda mapel
     * yang belum tuntas. Angka ini adalah KKM sekolah dan belum disimpan di
     * database, jadi nilainya declared di satu tempat agar dashboard dan
     * halaman rekap tidak berbeda.
     */
    public const PASSING_SCORE = 75.0;

    /**
     * Ambang bawah tiap predikat, dari tertinggi ke terendah.
     *
     * @var array<string, float>
     */
    private const PREDICATE_THRESHOLDS = [
        'A' => 90.0,
        'B' => 80.0,
        'C' => 75.0,
        'D' => 70.0,
        'E' => 0.0,
    ];

    /**
     * @var array<string, string>
     */
    private const PREDICATE_LABELS = [
        'A' => 'Sangat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Perlu Bimbingan',
        'E' => 'Belum Tuntas',
    ];

    /**
     * Ringkasan lengkap untuk satu siswa.
     *
     * @return array{
     *     gradebooks: Collection<int, Gradebook>,
     *     subjects: Collection<int, array<string, mixed>>,
     *     overall: array{average: ?float, predicate: ?string, predicateLabel: ?string, passing: ?bool, subjectCount: int, total: int, graded: int},
     *     tasks: array{total: int, submitted: int, pending: int, overdue: int}
     * }
     */
    public function forStudent(StudentProfile $student): array
    {
        $gradebooks = $this->activeGradebooks($student);
        $summaries = $this->gradebookSummaries($student, $gradebooks);

        $subjects = $gradebooks
            ->map(function (Gradebook $gradebook) use ($summaries) {
                $average = $summaries[$gradebook->id]['average'] ?? null;

                return [
                    'gradebook' => $gradebook,
                    'subject' => $gradebook->teachingAssignment?->subject,
                    'teacher' => $gradebook->teachingAssignment?->teacher,
                    'schoolClass' => $gradebook->teachingAssignment?->schoolClass,
                    'semester' => $gradebook->teachingAssignment?->semester,
                    'average' => $average,
                    'predicate' => $this->predicate($average),
                    'passing' => $average === null ? null : $average >= self::PASSING_SCORE,
                    'graded' => $summaries[$gradebook->id]['graded'] ?? 0,
                    'total' => $summaries[$gradebook->id]['total'] ?? 0,
                ];
            })
            ->sortBy(fn (array $row) => $row['subject']?->name ?? 'Mata Pelajaran')
            ->values();

        $averages = $subjects
            ->map(fn (array $row) => $row['average'])
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value);

        $overallAverage = $averages->isNotEmpty() ? round($averages->avg(), 2) : null;
        $predicate = $this->predicate($overallAverage);

        return [
            'gradebooks' => $gradebooks,
            'subjects' => $subjects,
            'overall' => [
                'average' => $overallAverage,
                'predicate' => $predicate,
                'predicateLabel' => $predicate === null ? null : self::PREDICATE_LABELS[$predicate],
                'passing' => $overallAverage === null ? null : $overallAverage >= self::PASSING_SCORE,
                'subjectCount' => $subjects->count(),
                'graded' => $subjects->sum('graded'),
                'total' => $subjects->sum('total'),
            ],
            'tasks' => $this->taskSummary($student),
        ];
    }

    /**
     * Buku nilai aktif tempat siswa terdaftar sebagai anggota.
     *
     * @return Collection<int, Gradebook>
     */
    public function activeGradebooks(StudentProfile $student): Collection
    {
        return $student->gradebookMemberships()
            ->with([
                'gradebook.teachingAssignment.subject',
                'gradebook.teachingAssignment.teacher',
                'gradebook.teachingAssignment.schoolClass',
                'gradebook.teachingAssignment.semester',
                'gradebook.columns' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order'),
                'gradebook.columns.sourceColumns',
            ])
            ->whereHas('gradebook', fn ($query) => $query->where('is_active', true))
            ->get()
            ->map(fn ($membership) => $membership->gradebook)
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Ringkasan nilai per buku nilai, dihitung dengan satu kueri skor.
     *
     * @param  SupportCollection<int, Gradebook>  $gradebooks
     * @return array<int, array{average: ?float, graded: int, total: int}>
     */
    public function gradebookSummaries(StudentProfile $student, ?SupportCollection $gradebooks = null): array
    {
        $gradebooks ??= $this->activeGradebooks($student);

        $columnIds = $gradebooks
            ->flatMap(fn (Gradebook $gradebook) => $gradebook->columns->pluck('id'))
            ->unique()
            ->values();

        $scores = $this->scoresByColumn($student, $columnIds);

        $summaries = [];

        foreach ($gradebooks as $gradebook) {
            $values = [];
            $graded = 0;

            foreach ($gradebook->columns as $column) {
                $value = $this->resolveColumnValue($column, $scores);

                $values[$column->id] = $value;

                if ($value !== null) {
                    $graded++;
                }
            }

            $included = $gradebook->columns
                ->filter(fn (GradebookColumn $column) => $column->is_included_in_average)
                ->map(fn (GradebookColumn $column) => $values[$column->id])
                ->filter(fn ($value) => $value !== null)
                ->map(fn ($value) => (float) $value);

            $summaries[$gradebook->id] = [
                'average' => $included->isNotEmpty() ? round($included->avg(), 2) : null,
                'graded' => $graded,
                'total' => $gradebook->columns->count(),
            ];
        }

        return $summaries;
    }

    /**
     * Progres pengumpulan tugas yang diterbitkan guru untuk kelas siswa.
     *
     * @return array{total: int, submitted: int, pending: int, overdue: int}
     */
    public function taskSummary(StudentProfile $student): array
    {
        $classIds = $student->classEnrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        $assessments = Assessment::query()
            ->where('status', AssessmentStatus::Published->value)
            ->where('submission_required', true)
            ->whereHas('teachingAssignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds)->where('is_active', true);
            })
            ->with(['submissions' => fn ($query) => $query->where('student_id', $student->id)])
            ->get();

        $submitted = 0;
        $overdue = 0;

        foreach ($assessments as $assessment) {
            $submission = $assessment->submissions->first();
            $isDone = $submission !== null && $submission->status !== SubmissionStatus::Draft;

            if ($isDone) {
                $submitted++;
            }

            if (! $isDone && $assessment->due_at !== null && $assessment->due_at->isPast()) {
                $overdue++;
            }
        }

        $total = $assessments->count();

        return [
            'total' => $total,
            'submitted' => $submitted,
            'pending' => max($total - $submitted, 0),
            'overdue' => $overdue,
        ];
    }

    /**
     * Nilai satu kolom untuk siswa: kolom SCORE dibaca dari gradebook_scores,
     * kolom SUMMARY dihitung deterministik dari kolom sumbernya.
     *
     * @param  Collection<int, GradebookScore>  $scores
     */
    public function resolveColumnValue(GradebookColumn $column, Collection $scores): ?float
    {
        if ($column->column_type === GradebookColumnType::Score) {
            $score = $scores->get($column->id);

            return $score?->final_score !== null ? (float) $score->final_score : null;
        }

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
     * Predikat nilai memakai ambang batas yang sama untuk dashboard dan rekap.
     */
    public function predicate(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }

        foreach (self::PREDICATE_THRESHOLDS as $letter => $threshold) {
            if ($average >= $threshold) {
                return $letter;
            }
        }

        return 'E';
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

            if ($weight <= 0) {
                $weight = 1.0;
            }

            $weightedTotal += (float) $value * $weight;
            $weightTotal += $weight;
        }

        return $weightTotal > 0 ? round($weightedTotal / $weightTotal, 2) : null;
    }

    /**
     * @param  SupportCollection<int, int>  $columnIds
     * @return Collection<int, GradebookScore>
     */
    private function scoresByColumn(StudentProfile $student, SupportCollection $columnIds): Collection
    {
        if ($columnIds->isEmpty()) {
            return new Collection;
        }

        return GradebookScore::query()
            ->where('student_id', $student->id)
            ->whereIn('gradebook_column_id', $columnIds)
            ->get()
            ->keyBy('gradebook_column_id');
    }
}
