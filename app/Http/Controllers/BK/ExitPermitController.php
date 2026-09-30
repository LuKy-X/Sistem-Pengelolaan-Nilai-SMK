<?php

namespace App\Http\Controllers\BK;

use App\Enums\ExitPermitStatus;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\Controller;
use App\Http\Requests\BK\ApproveExitPermitRequest;
use App\Http\Requests\BK\RejectExitPermitRequest;
use App\Models\ExitPermit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExitPermitController extends Controller
{
    use HandlesDisciplinePoints, RecordsAuditTrail;

    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $baseQuery = ExitPermit::query()
            ->with(['student.currentEnrollment.schoolClass', 'reason', 'approver', 'appeal']);

        $statusCounts = (clone $baseQuery)
            ->reorder()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $permits = $baseQuery
            ->when(in_array($statusFilter, array_column(ExitPermitStatus::cases(), 'value'), true),
                fn (Builder $query) => $query->where('status', $statusFilter))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('student', function (Builder $studentQuery) use ($search) {
                    $studentQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('requested_at')
            ->paginate(10)
            ->withQueryString();

        $permitsOut = ExitPermit::query()
            ->with(['student.currentEnrollment.schoolClass', 'reason'])
            ->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
            ->whereNull('actual_return_at')
            ->orderBy('planned_return_at')
            ->get();

        $academicYear = $this->activeAcademicYear();

        return view('bk.exit-permits.index', [
            'permits' => $permits,
            'permitsOut' => $permitsOut,
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'academicYear' => $academicYear,
        ]);
    }

    public function show(ExitPermit $permit): View
    {
        Gate::authorize('view', $permit);

        $permit->load([
            'student.currentEnrollment.schoolClass',
            'reason',
            'approver',
            'appeal.decider',
        ]);

        $studentHistory = ExitPermit::query()
            ->with('reason')
            ->where('student_id', $permit->student_id)
            ->where('id', '!=', $permit->getKey())
            ->orderByDesc('requested_at')
            ->limit(5)
            ->get();

        $academicYear = $this->activeAcademicYear();
        $setting = $this->disciplineSetting($academicYear?->id);
        $balance = $this->pointBalance($permit->student_id, $setting, $academicYear?->id);

        return view('bk.exit-permits.show', [
            'permit' => $permit,
            'studentHistory' => $studentHistory,
            'balance' => $balance,
            'standing' => $this->disciplineStanding($balance, $setting),
            'setting' => $setting,
        ]);
    }

    public function approve(ApproveExitPermitRequest $request, ExitPermit $permit): RedirectResponse
    {
        if ($permit->status !== ExitPermitStatus::Pending) {
            return back()->with('error', 'Izin ini sudah diproses dan tidak dapat disetujui kembali.');
        }

        $validated = $request->validated();
        $studentName = $permit->loadMissing('student')->student?->full_name ?? 'siswa';

        DB::transaction(function () use ($permit, $validated, $request) {
            $permit->update([
                'status' => ExitPermitStatus::Approved,
                'approved_at' => now(),
                'approved_by' => $request->user()?->staffProfile?->id,
                'actual_exit_at' => now(),
                'approval_note' => $validated['approval_note'] ?? null,
            ]);

            $this->audit('EXIT_PERMIT_APPROVED', $permit, [
                'status' => $permit->status->value,
                'approval_note' => $permit->approval_note,
            ]);
        });

        return redirect()
            ->route('counselor.exit-permits.index')
            ->with('success', "Izin {$studentName} disetujui. Timer kepulangan sudah aktif.");
    }

    public function reject(RejectExitPermitRequest $request, ExitPermit $permit): RedirectResponse
    {
        if ($permit->status !== ExitPermitStatus::Pending) {
            return back()->with('error', 'Izin ini sudah diproses dan tidak dapat ditolak kembali.');
        }

        $validated = $request->validated();
        $studentName = $permit->loadMissing('student')->student?->full_name ?? 'siswa';

        DB::transaction(function () use ($permit, $validated, $request) {
            $permit->update([
                'status' => ExitPermitStatus::Rejected,
                'approved_at' => now(),
                'approved_by' => $request->user()?->staffProfile?->id,
                'rejection_note' => $validated['rejection_note'],
            ]);

            $this->audit('EXIT_PERMIT_REJECTED', $permit, [
                'status' => $permit->status->value,
                'rejection_note' => $permit->rejection_note,
            ]);
        });

        return redirect()
            ->route('counselor.exit-permits.index')
            ->with('success', "Izin {$studentName} ditolak. Alasan penolakan tersimpan di riwayat.");
    }

    public function complete(ExitPermit $permit): RedirectResponse
    {
        Gate::authorize('process', $permit);

        if (! in_array($permit->status, [ExitPermitStatus::Approved, ExitPermitStatus::Late], true)
            || $permit->actual_return_at !== null) {
            return back()->with('error', 'Izin ini tidak sedang aktif sehingga tidak dapat ditandai sudah kembali.');
        }

        $isLate = $permit->planned_return_at->isPast();
        $minutesLate = (int) $permit->planned_return_at->diffInMinutes(now());
        $studentName = $permit->loadMissing('student')->student?->full_name ?? 'siswa';

        DB::transaction(function () use ($permit, $isLate) {
            $permit->update([
                'actual_return_at' => now(),
                'status' => $isLate ? ExitPermitStatus::Late : ExitPermitStatus::Completed,
            ]);

            $this->audit('EXIT_PERMIT_COMPLETED', $permit, [
                'status' => $permit->status->value,
                'actual_return_at' => $permit->actual_return_at?->toDateTimeString(),
            ]);
        });

        return redirect()
            ->route('counselor.exit-permits.index')
            ->with('success', $isLate
                ? "Kepulangan {$studentName} dicatat sebagai terlambat {$minutesLate} menit. Siswa dapat mengajukan banding."
                : "Kepulangan {$studentName} dicatat tepat waktu.");
    }
}
