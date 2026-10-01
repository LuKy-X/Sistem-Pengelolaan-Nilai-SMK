<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeachingAssignmentRequest;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);

        $assignments = TeachingAssignment::with([
            'teacher',
            'subject',
            'schoolClass.department',
            'schoolClass.gradeLevel',
            'semester.academicYear',
        ])
            ->when($selectedSemesterId, function ($query, $semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $semesters = Semester::with('academicYear')->latest('start_date')->get();
        $teachers = TeacherProfile::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.academic.teaching-assignments.index', compact(
            'assignments',
            'semesters',
            'teachers',
            'subjects',
            'classes',
            'selectedSemesterId'
        ));
    }

    public function store(StoreTeachingAssignmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['weekly_hours'] = $validated['weekly_hours'] ?? 2;

        // Cek duplikasi
        $exists = TeachingAssignment::where([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'semester_id' => $validated['semester_id'],
        ])->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Penugasan mengajar untuk guru, mapel, kelas, dan semester tersebut sudah ada.');
        }

        TeachingAssignment::create($validated);

        return redirect()->route('admin.academic.teaching-assignments.index', ['semester_id' => $validated['semester_id']])
            ->with('success', 'Penugasan guru mengajar berhasil disimpan.');
    }

    public function update(Request $request, TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $validated = $request->validate([
            'weekly_hours' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $teachingAssignment->update([
            'weekly_hours' => $validated['weekly_hours'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->back()->with('success', 'Data penugasan mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingAssignment $teachingAssignment): RedirectResponse
    {
        if ($teachingAssignment->gradebooks()->exists() || $teachingAssignment->classJournals()->exists()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus penugasan mengajar yang sudah memiliki buku nilai atau catatan jurnal absensi.');
        }

        $teachingAssignment->delete();

        return redirect()->back()->with('success', 'Penugasan guru mengajar berhasil dihapus.');
    }
}
