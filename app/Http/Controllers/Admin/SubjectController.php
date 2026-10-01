<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->input('category');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $subjects = Subject::with('department')
            ->withCount('teachingAssignments')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(! empty($category), function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when(! empty($departmentId), function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', (bool) $status);
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.academic.subjects.index', compact(
            'subjects',
            'departments',
            'search',
            'category',
            'departmentId',
            'status'
        ));
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
