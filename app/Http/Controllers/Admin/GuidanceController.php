<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineRecord;
use App\Models\ExitPermit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuidanceController extends Controller
{
    public function index(Request $request): View
    {
        $permitStats = [
            'total' => ExitPermit::count(),
            'approved' => ExitPermit::where('status', 'APPROVED')->count(),
            'pending' => ExitPermit::where('status', 'PENDING')->count(),
            'completed' => ExitPermit::where('status', 'COMPLETED')->count(),
            'late' => ExitPermit::where('status', 'LATE')->count(),
        ];

        $recentPermits = ExitPermit::with(['student', 'reason'])
            ->latest()
            ->take(10)
            ->get();

        $disciplineStats = [
            'total_records' => DisciplineRecord::count(),
            'violations' => DisciplineRecord::where('points_delta', '<', 0)->count(),
            'rewards' => DisciplineRecord::where('points_delta', '>', 0)->count(),
        ];

        $recentDisciplineRecords = DisciplineRecord::with(['student', 'category'])
            ->latest('occurred_at')
            ->take(10)
            ->get();

        $disciplinaryLetters = DisciplinaryLetter::with(['student', 'academicYear'])
            ->latest('issued_at')
            ->take(10)
            ->get();

        return view('admin.guidance.index', compact(
            'permitStats',
            'recentPermits',
            'disciplineStats',
            'recentDisciplineRecords',
            'disciplinaryLetters'
        ));
    }
}
