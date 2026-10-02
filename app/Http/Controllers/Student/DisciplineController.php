<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\Controller;
use App\Models\DisciplineCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    use HandlesDisciplinePoints;

    public function index(): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        // Kalkulasi saldo dan klasifikasi memakai logika yang sama persis dengan modul BK.
        $academicYear = $this->activeAcademicYear();
        $setting = $this->disciplineSetting($academicYear?->id);
        $balance = $this->pointBalance($student->id, $setting, $academicYear?->id);
        $standing = $this->disciplineStanding($balance, $setting);

        $records = $student->disciplineRecords()
            ->when($academicYear !== null, fn ($query) => $query->where('academic_year_id', $academicYear->id))
            ->with('category')
            ->orderByDesc('occurred_at')
            ->paginate(10);

        $letters = $student->disciplinaryLetters()
            ->when($academicYear !== null, fn ($query) => $query->where('academic_year_id', $academicYear->id))
            ->orderByDesc('issued_at')
            ->get();

        $ruleCategories = DisciplineCategory::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (DisciplineCategory $category) => $category->type->value);

        return view('student.discipline.index', compact(
            'student',
            'academicYear',
            'setting',
            'balance',
            'standing',
            'records',
            'letters',
            'ruleCategories',
        ));
    }
}
