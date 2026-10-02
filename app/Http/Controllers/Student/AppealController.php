<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppealDecision;
use App\Http\Controllers\Controller;
use App\Models\ExitPermitAppeal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppealController extends Controller
{
    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $decisionFilter = $request->query('decision');

        $baseQuery = ExitPermitAppeal::query()
            ->whereHas('exitPermit', fn (Builder $query) => $query->where('student_id', $student->id))
            ->with(['exitPermit.reason', 'decider']);

        $decisionCounts = (clone $baseQuery)
            ->reorder()
            ->selectRaw('decision, COUNT(*) as total')
            ->groupBy('decision')
            ->pluck('total', 'decision');

        $appeals = $baseQuery
            ->when(
                in_array($decisionFilter, array_column(AppealDecision::cases(), 'value'), true),
                fn (Builder $query) => $query->where('decision', $decisionFilter)
            )
            ->orderByDesc('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('student.appeals.index', compact('student', 'appeals', 'decisionCounts', 'decisionFilter'));
    }
}
