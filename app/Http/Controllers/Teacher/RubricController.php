<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RubricController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $rubrics = Rubric::with(['criteria', 'assessments'])
            ->where('created_by', $teacher->id)
            ->latest()
            ->get();

        return view('teacher.rubrics.index', compact('rubrics'));
    }

    public function create(): View
    {
        return view('teacher.rubrics.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.criterion' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
            'criteria.*.max_points' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $teacher) {
            $rubric = Rubric::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'created_by' => $teacher->id,
                'status' => 'PUBLISHED',
            ]);

            foreach ($validated['criteria'] as $index => $c) {
                RubricCriterion::create([
                    'rubric_id' => $rubric->id,
                    'criterion' => $c['criterion'],
                    'description' => $c['description'] ?? null,
                    'max_points' => (float) $c['max_points'],
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('teacher.rubrics.index')->with('success', 'Rubrik penilaian baru berhasil dibuat.');
    }

    public function show(Rubric $rubric): View
    {
        $teacher = Auth::user()->teacherProfile;
        if ($rubric->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $rubric->load(['criteria' => fn ($q) => $q->orderBy('sort_order'), 'assessments']);

        return view('teacher.rubrics.show', compact('rubric'));
    }

    public function edit(Rubric $rubric): View|RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;
        if ($rubric->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($rubric->assessments()->exists()) {
            return redirect()->route('teacher.rubrics.show', $rubric)
                ->with('error', 'Rubrik penilaian ini sudah digunakan dalam penugasan sehingga tidak dapat diubah agar tidak merusak data penilaian siswa.');
        }

        $rubric->load(['criteria' => fn ($q) => $q->orderBy('sort_order')]);

        return view('teacher.rubrics.edit', compact('rubric'));
    }

    public function update(Request $request, Rubric $rubric): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;
        if ($rubric->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($rubric->assessments()->exists()) {
            return redirect()->route('teacher.rubrics.show', $rubric)
                ->with('error', 'Rubrik penilaian ini sudah digunakan dalam penugasan dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.criterion' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
            'criteria.*.max_points' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $rubric) {
            $rubric->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $rubric->criteria()->delete();

            foreach ($validated['criteria'] as $index => $c) {
                RubricCriterion::create([
                    'rubric_id' => $rubric->id,
                    'criterion' => $c['criterion'],
                    'description' => $c['description'] ?? null,
                    'max_points' => (float) $c['max_points'],
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('teacher.rubrics.show', $rubric)->with('success', 'Rubrik penilaian berhasil diperbarui.');
    }

    public function destroy(Rubric $rubric): RedirectResponse
    {
        $teacher = Auth::user()->teacherProfile;
        if ($rubric->created_by !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($rubric->assessments()->exists()) {
            return redirect()->route('teacher.rubrics.show', $rubric)
                ->with('error', 'Rubrik penilaian ini sudah digunakan dalam penugasan dan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($rubric) {
            $rubric->criteria()->delete();
            $rubric->delete();
        });

        return redirect()->route('teacher.rubrics.index')->with('success', 'Rubrik penilaian berhasil dihapus.');
    }
}
