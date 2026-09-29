<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\DepartmentCompetency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount(['classes', 'competencies', 'facilities'])
            ->latest()
            ->get();

        return view('admin.academic.departments.index', compact('departments'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Department::create($validated);

        return redirect()->route('admin.academic.departments.index')
            ->with('success', 'Jurusan / Kompetensi Keahlian berhasil ditambahkan.');
    }

    public function show(Department $department): View
    {
        $department->load(['competencies', 'facilities', 'classes.gradeLevel']);

        return view('admin.academic.departments.show', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $department->update($validated);

        return redirect()->route('admin.academic.departments.show', $department)
            ->with('success', 'Data jurusan berhasil diperbarui.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->classes()->exists() || $department->subjects()->exists()) {
            return redirect()->route('admin.academic.departments.index')
                ->with('error', 'Tidak dapat menghapus jurusan yang masih memiliki rombel atau mata pelajaran.');
        }

        $department->delete();

        return redirect()->route('admin.academic.departments.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }

    public function storeCompetency(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $department->competencies()->create($validated);

        return redirect()->back()->with('success', 'Kompetensi kejuruan berhasil ditambahkan.');
    }

    public function destroyCompetency(DepartmentCompetency $competency): RedirectResponse
    {
        $competency->delete();

        return redirect()->back()->with('success', 'Kompetensi berhasil dihapus.');
    }
}
