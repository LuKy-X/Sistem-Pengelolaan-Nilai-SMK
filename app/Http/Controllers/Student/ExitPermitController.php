<?php

namespace App\Http\Controllers\Student;

use App\Enums\ExitPermitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreAppealRequest;
use App\Http\Requests\Student\StoreExitPermitRequest;
use App\Models\ExitPermit;
use App\Models\ExitPermitReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExitPermitController extends Controller
{
    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $statusFilter = $request->query('status');

        $permits = $student->exitPermits()
            ->with(['reason', 'approver', 'appeal'])
            ->when(
                in_array($statusFilter, array_column(ExitPermitStatus::cases(), 'value'), true),
                fn (Builder $query) => $query->where('status', $statusFilter)
            )
            ->orderByDesc('requested_at')
            ->paginate(10)
            ->withQueryString();

        $activePermit = $student->exitPermits()
            ->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
            ->whereNull('actual_return_at')
            ->with('reason')
            ->latest('approved_at')
            ->first();

        $statusCounts = $student->exitPermits()
            ->reorder()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('student.exit-permits.index', compact('student', 'permits', 'activePermit', 'statusCounts', 'statusFilter'));
    }

    public function create(): View|RedirectResponse
    {
        Gate::authorize('create', ExitPermit::class);

        $student = Auth::user()->studentProfile;

        if ($this->hasOpenPermit($student->id)) {
            return redirect()
                ->route('student.exit-permits.index')
                ->with('error', 'Anda masih memiliki izin yang menunggu persetujuan atau sedang aktif. Selesaikan dulu izin tersebut.');
        }

        $reasons = ExitPermitReason::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('student.exit-permits.create', compact('student', 'reasons'));
    }

    public function store(StoreExitPermitRequest $request): RedirectResponse
    {
        $student = $request->user()->studentProfile;

        if ($this->hasOpenPermit($student->id)) {
            return redirect()
                ->route('student.exit-permits.index')
                ->with('error', 'Anda masih memiliki izin yang menunggu persetujuan atau sedang aktif.');
        }

        $validated = $request->validated();

        $permit = ExitPermit::create([
            'student_id' => $student->id,
            'reason_id' => $validated['reason_id'],
            'reason_detail' => $validated['reason_detail'],
            'requested_at' => now(),
            'planned_exit_at' => $validated['planned_exit_at'],
            'planned_return_at' => $validated['planned_return_at'],
            'status' => ExitPermitStatus::Pending,
        ]);

        return redirect()
            ->route('student.exit-permits.show', $permit)
            ->with('success', 'Pengajuan izin keluar terkirim. Menunggu persetujuan Guru BK.');
    }

    public function show(ExitPermit $permit): View
    {
        Gate::authorize('view', $permit);

        $permit->load(['reason', 'approver', 'appeal.decider']);

        return view('student.exit-permits.show', [
            'student' => Auth::user()->studentProfile,
            'permit' => $permit,
        ]);
    }

    public function appeal(StoreAppealRequest $request, ExitPermit $permit): RedirectResponse
    {
        if ($permit->status !== ExitPermitStatus::Late) {
            return back()->with('error', 'Banding hanya dapat diajukan untuk izin yang tercatat terlambat kembali.');
        }

        if ($permit->appeal()->exists()) {
            return back()->with('error', 'Banding untuk izin ini sudah pernah diajukan.');
        }

        $permit->appeal()->create([
            'submitted_at' => now(),
            'reason' => $request->validated()['reason'],
        ]);

        return redirect()
            ->route('student.exit-permits.show', $permit)
            ->with('success', 'Banding keterlambatan terkirim. Menunggu keputusan Guru BK.');
    }

    /**
     * Izin yang masih berjalan: menunggu persetujuan, atau sedang aktif di luar sekolah.
     */
    private function hasOpenPermit(int $studentId): bool
    {
        return ExitPermit::query()
            ->where('student_id', $studentId)
            ->where(function (Builder $query) {
                $query->where('status', ExitPermitStatus::Pending->value)
                    ->orWhere(function (Builder $inner) {
                        $inner->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
                            ->whereNull('actual_return_at');
                    });
            })
            ->exists();
    }
}
