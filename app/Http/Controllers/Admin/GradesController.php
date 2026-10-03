<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GradesController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');
        $semesterId = $request->query('semester_id');
        $subjectId = $request->query('subject_id');
        $teacherId = $request->query('teacher_id');
        $status = $request->query('status');

        $gradebooks = Gradebook::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.semester.academicYear',
            'columns',
            'students',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('teachingAssignment.subject', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('teachingAssignment.teacher', fn ($tq) => $tq->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('teachingAssignment.schoolClass', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($classId, function ($query, $classId) {
                $query->whereHas('teachingAssignment', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            })
            ->when($semesterId, function ($query, $semesterId) {
                $query->whereHas('teachingAssignment', function ($q) use ($semesterId) {
                    $q->where('semester_id', $semesterId);
                });
            })
            ->when($subjectId, function ($query, $subjectId) {
                $query->whereHas('teachingAssignment', function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                });
            })
            ->when($teacherId, function ($query, $teacherId) {
                $query->whereHas('teachingAssignment', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === '1');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();
        $semesters = Semester::with('academicYear')->latest('start_date')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = TeacherProfile::where('status', 'ACTIVE')->orderBy('full_name')->get();

        $stats = [
            'total' => Gradebook::count(),
            'active' => Gradebook::where('is_active', true)->count(),
            'columns' => GradebookColumn::count(),
            'teachers' => TeacherProfile::has('teachingAssignments')->count(),
        ];

        return view('admin.grades.index', compact(
            'gradebooks',
            'classes',
            'semesters',
            'subjects',
            'teachers',
            'search',
            'classId',
            'semesterId',
            'subjectId',
            'teacherId',
            'status',
            'stats'
        ));
    }

    public function show(Gradebook $gradebook): View
    {
        $data = $this->getGradebookData($gradebook);

        return view('admin.grades.show', $data);
    }

    /**
     * Tampilkan halaman pratinjau cetak PDF dokumen rekap nilai siswa.
     */
    public function exportPdf(Gradebook $gradebook): View
    {
        $data = $this->getGradebookData($gradebook);
        $schoolProfile = SchoolProfile::first();

        return view('admin.grades.pdf', array_merge($data, [
            'schoolProfile' => $schoolProfile,
        ]));
    }

    /**
     * Unduh file Spreadsheet Excel (.xls) rekap buku nilai siswa.
     */
    public function exportExcel(Gradebook $gradebook): Response
    {
        $data = $this->getGradebookData($gradebook);
        $schoolProfile = SchoolProfile::first();

        $assignment = $gradebook->teachingAssignment;
        $className = $assignment?->schoolClass?->name ?? 'Kelas';
        $subjectName = $assignment?->subject?->name ?? 'Mapel';

        $html = view('admin.grades.excel', array_merge($data, [
            'schoolProfile' => $schoolProfile,
        ]))->render();

        $cleanClass = preg_replace('/[^A-Za-z0-9_-]/', '_', $className);
        $cleanSubject = preg_replace('/[^A-Za-z0-9_-]/', '_', $subjectName);
        $fileName = 'Rekap_Nilai_'.$cleanClass.'_'.$cleanSubject.'_'.date('Ymd_His').'.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Helper loader data buku nilai dan matriks skor siswa.
     *
     * @return array{gradebook: Gradebook, categories: Collection, columns: Collection, students: Collection, scoresMatrix: array, columnAverages: array}
     */
    private function getGradebookData(Gradebook $gradebook): array
    {
        $gradebook->load([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.semester.academicYear',
            'categories.columns' => fn ($q) => $q->orderBy('sort_order'),
            'columns.category',
            'columns.sourceColumns',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student.user',
        ]);

        $columns = $gradebook->columns;
        $categories = $gradebook->categories;
        $students = $gradebook->students->sortBy('student.full_name');

        // Fetch all scores for this gradebook mapped as [student_id][column_id] => score
        $scoresCollection = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get();

        $scoresMatrix = [];
        foreach ($scoresCollection as $score) {
            $scoresMatrix[$score->student_id][$score->gradebook_column_id] = $score->final_score;
        }

        // Dynamically compute scores for SUMMARY columns based on their sourceColumns
        foreach ($columns as $column) {
            if ($column->column_type === GradebookColumnType::Summary) {
                $sourceColIds = $column->sourceColumns->pluck('id')->all();
                if (empty($sourceColIds)) {
                    $sourceColIds = $columns->filter(fn ($c) => $c->sort_order < $column->sort_order && $c->column_type === GradebookColumnType::Score)->pluck('id')->all();
                }

                foreach ($students as $studentEntry) {
                    $stId = $studentEntry->student_id;
                    $sourceScores = [];
                    $weightedSum = 0;
                    $totalWeight = 0;

                    foreach ($sourceColIds as $sId) {
                        $val = $scoresMatrix[$stId][$sId] ?? null;
                        if ($val !== null && is_numeric($val)) {
                            $scoreFloat = (float) $val;
                            $sourceScores[] = $scoreFloat;

                            $srcCol = $columns->firstWhere('id', $sId);
                            $w = (float) ($srcCol?->weight ?? 1);
                            if ($w <= 0) {
                                $w = 1;
                            }

                            $weightedSum += ($scoreFloat * $w);
                            $totalWeight += $w;
                        }
                    }

                    if (! empty($sourceScores)) {
                        $calculated = match ($column->calculation_type) {
                            GradebookCalculationType::Sum => round(array_sum($sourceScores), 2),
                            GradebookCalculationType::WeightedAverage => $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : round(array_sum($sourceScores) / count($sourceScores), 2),
                            default => round(array_sum($sourceScores) / count($sourceScores), 2),
                        };
                        $scoresMatrix[$stId][$column->id] = $calculated;
                    }
                }
            }
        }

        // Summary calculations per column
        $columnAverages = [];
        foreach ($columns as $column) {
            $colScores = collect();
            foreach ($students as $st) {
                $val = $scoresMatrix[$st->student_id][$column->id] ?? null;
                if ($val !== null && is_numeric($val)) {
                    $colScores->push((float) $val);
                }
            }
            $columnAverages[$column->id] = $colScores->isNotEmpty() ? round($colScores->avg(), 1) : '-';
        }

        return [
            'gradebook' => $gradebook,
            'categories' => $categories,
            'columns' => $columns,
            'students' => $students,
            'scoresMatrix' => $scoresMatrix,
            'columnAverages' => $columnAverages,
        ];
    }
}
