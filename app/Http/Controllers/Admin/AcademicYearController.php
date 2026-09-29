<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $activeYear = AcademicYear::with('semesters')->where('is_active', true)->first();
        $academicYears = AcademicYear::with('semesters')->latest('start_date')->get();

        return view('admin.academic.years.index', compact('activeYear', 'academicYears'));
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.academic.years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $year): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive && ! $year->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $year->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.academic.years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function toggleActive(AcademicYear $year): RedirectResponse
    {
        if (! $year->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            $year->update(['is_active' => true]);
            $message = "Tahun ajaran {$year->name} berhasil diaktifkan.";
        } else {
            $year->update(['is_active' => false]);
            $message = "Tahun ajaran {$year->name} dinonaktifkan.";
        }

        return redirect()->route('admin.academic.years.index')->with('success', $message);
    }

    public function destroy(AcademicYear $year): RedirectResponse
    {
        if ($year->classes()->exists() || $year->semesters()->exists()) {
            return redirect()->route('admin.academic.years.index')
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang memiliki data semester atau rombel terhubung.');
        }

        $year->delete();

        return redirect()->route('admin.academic.years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
