<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\StudentGradeNote;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeNoteController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.gradeLevel',
            'schoolClass.department',
            'subject',
            'semester.academicYear',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id');
        $selectedAssignment = $selectedAssignmentId ? $assignments->firstWhere('id', $selectedAssignmentId) : null;

        $notes = collect();
        $enrolledStudents = collect();

        if ($selectedAssignment) {
            $notes = StudentGradeNote::with('student')
                ->where('teaching_assignment_id', $selectedAssignment->id)
                ->latest()
                ->get();

            $enrolledStudents = ClassEnrollment::with('student')
                ->where('class_id', $selectedAssignment->class_id)
                ->where('status', 'ACTIVE')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('full_name');
        }

        return view('teacher.grade-notes.index', compact(
            'assignments',
            'selectedAssignment',
            'notes',
            'enrolledStudents'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'student_id' => ['required', 'exists:student_profiles,id'],
            'category' => ['required', 'string', 'max:50'],
            'note' => ['required', 'string'],
        ]);

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);
        if ($assignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        StudentGradeNote::create([
            'teaching_assignment_id' => $assignment->id,
            'student_id' => $validated['student_id'],
            'teacher_id' => $teacher->id,
            'category' => $validated['category'],
            'note' => $validated['note'],
        ]);

        return redirect()->route('teacher.grade-notes.index', ['assignment_id' => $assignment->id])
            ->with('success', 'Catatan nilai siswa berhasil disimpan.');
    }

    public function destroy(StudentGradeNote $gradeNote): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        if ($gradeNote->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $assignmentId = $gradeNote->teaching_assignment_id;
        $gradeNote->delete();

        return redirect()->route('teacher.grade-notes.index', ['assignment_id' => $assignmentId])
            ->with('success', 'Catatan nilai berhasil dihapus.');
    }
}
