<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with([
            'academicYear',
            'department',
            'gradeLevel',
            'homeroomTeacher',
        ])
            ->withCount(['enrollments', 'teachingAssignments'])
            ->latest()
            ->get();

        $academicYears = AcademicYear::latest('start_date')->get();
        $departments = Department::where('is_active', true)->get();
        $gradeLevels = GradeLevel::all();
        $teachers = TeacherProfile::where('status', 'ACTIVE')->orderBy('full_name')->get();

        return view('admin.academic.classes.index', compact(
            'classes',
            'academicYears',
            'departments',
            'gradeLevels',
            'teachers'
        ));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        SchoolClass::create($validated);

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Rombongan belajar (kelas) berhasil dibuat.');
    }

    public function show(SchoolClass $class): View
    {
        $class->load([
            'academicYear',
            'department',
            'gradeLevel',
            'homeroomTeacher',
            'enrollments.student',
            'teachingAssignments.subject',
            'teachingAssignments.teacher',
        ]);

        return view('admin.academic.classes.show', compact('class'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $class->update($validated);

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Data rombongan belajar berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->enrollments()->exists() || $class->teachingAssignments()->exists()) {
            return redirect()->route('admin.academic.classes.index')
                ->with('error', 'Tidak dapat menghapus kelas yang sudah memiliki siswa terdaftar atau jadwal mengajar.');
        }

        $class->delete();

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Rombongan belajar berhasil dihapus.');
    }
}
