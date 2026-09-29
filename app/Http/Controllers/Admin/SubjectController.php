<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::with('department')
            ->withCount('teachingAssignments')
            ->orderBy('code')
            ->get();

        $departments = Department::where('is_active', true)->get();

        return view('admin.academic.subjects.index', compact('subjects', 'departments'));
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Subject::create($validated);

        return redirect()->route('admin.academic.subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $subject->update($validated);

        return redirect()->route('admin.academic.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->teachingAssignments()->exists()) {
            return redirect()->route('admin.academic.subjects.index')
                ->with('error', 'Tidak dapat menghapus mata pelajaran yang memiliki penugasan guru mengajar.');
        }

        $subject->delete();

        return redirect()->route('admin.academic.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
