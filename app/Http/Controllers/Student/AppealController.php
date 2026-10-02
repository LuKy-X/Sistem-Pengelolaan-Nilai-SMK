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

    /**
     * Detail satu banding. Banding milik siswa lain harus ditolak meskipun
     * statusnya masih pending.
     */
    public function show(ExitPermitAppeal $appeal): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $owns = ExitPermitAppeal::query()
            ->whereKey($appeal->getKey())
            ->whereHas('exitPermit', fn (Builder $query) => $query->where('student_id', $student->id))
            ->exists();

        abort_unless($owns, 403, 'Banding ini bukan milik Anda.');

        $appeal->load(['exitPermit.reason', 'decider.user']);

        return view('student.appeals.show', compact('student', 'appeal'));
    }
}
