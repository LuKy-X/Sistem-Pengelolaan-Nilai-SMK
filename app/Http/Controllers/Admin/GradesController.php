<?php

namespace App\Http\Controllers\Admin;

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
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.semester',
            'columns',
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

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $semesters = Semester::with('academicYear')->latest('start_date')->get();

        return view('admin.grades.index', compact('gradebooks', 'classes', 'semesters', 'classId', 'semesterId'));
    }

    public function show(Gradebook $gradebook): View
    {
        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'columns',
            'students.student',
        ]);

        $columns = $gradebook->columns()->orderBy('order_index')->get();
        $students = $gradebook->students()->with('student')->get();

        $scores = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->get()
            ->groupBy(function ($item) {
                return $item->student_id.'_'.$item->gradebook_column_id;
            });

        return view('admin.grades.show', compact('gradebook', 'columns', 'students', 'scores'));
    }
}
