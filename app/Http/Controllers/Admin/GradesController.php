<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Gradebook;
use App\Models\GradebookScore;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradesController extends Controller
{
    public function index(Request $request): View
    {
        $classId = $request->query('class_id');
        $semesterId = $request->query('semester_id');

        $gradebooks = Gradebook::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.semester.academicYear',
            'columns',
            'students',
        ])
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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();
        $semesters = Semester::with('academicYear')->latest('start_date')->get();

        return view('admin.grades.index', compact('gradebooks', 'classes', 'semesters', 'classId', 'semesterId'));
    }

    public function show(Gradebook $gradebook): View
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

        return view('admin.grades.show', compact(
            'gradebook',
            'categories',
            'columns',
            'students',
            'scoresMatrix',
            'columnAverages'
        ));
    }
}
