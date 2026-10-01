<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSemesterRequest;
use App\Http\Requests\Admin\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        $semesters = Semester::with('academicYear')->latest('start_date')->get();
        $academicYears = AcademicYear::latest('start_date')->get();

        return view('admin.academic.semesters.index', compact('semesters', 'academicYears'));
    }

    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        $semester = Semester::create([
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'semester_number' => $validated['semester_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        if ($isActive && $semester->academicYear && ! $semester->academicYear->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            $semester->academicYear->update(['is_active' => true]);
        }

        return redirect()->back()->with('success', 'Semester berhasil ditambahkan.');
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive && ! $semester->is_active) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        $semester->update([
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'semester_number' => $validated['semester_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        if ($isActive && $semester->academicYear && ! $semester->academicYear->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            $semester->academicYear->update(['is_active' => true]);
        }

        return redirect()->back()->with('success', 'Semester berhasil diperbarui.');
    }

    public function toggleActive(Semester $semester): RedirectResponse
    {
        if (! $semester->is_active) {
            Semester::where('is_active', true)->update(['is_active' => false]);
            $semester->update(['is_active' => true]);

            if ($semester->academicYear && ! $semester->academicYear->is_active) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
                $semester->academicYear->update(['is_active' => true]);
            }

            $message = "Semester {$semester->name} berhasil diaktifkan.";
        } else {
            $semester->update(['is_active' => false]);
            $message = "Semester {$semester->name} dinonaktifkan.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->teachingAssignments()->exists()) {
            return redirect()->back()
                ->with('error', "Tidak dapat menghapus semester '{$semester->name}' karena sudah memiliki data penugasan guru terhubung.");
        }

        $semesterName = $semester->name;
        $semester->delete();

        return redirect()->back()->with('success', "Semester '{$semesterName}' berhasil dihapus.");
    }
}
