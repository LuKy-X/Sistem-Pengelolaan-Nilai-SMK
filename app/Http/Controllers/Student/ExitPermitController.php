<?php

namespace App\Http\Controllers\Student;

use App\Enums\ExitPermitStatus;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreAppealRequest;
use App\Http\Requests\Student\StoreExitPermitRequest;
use App\Models\ExitPermit;
use App\Models\ExitPermitReason;
use App\Models\LessonPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExitPermitController extends Controller
{
    use RecordsAuditTrail;

    public function index(Request $request): View
    {
        $student = Auth::user()->studentProfile;

        abort_if($student === null, 403, 'Profil siswa tidak ditemukan. Hubungi admin sekolah.');

        $statusFilter = $request->query('status');

        $permits = $student->exitPermits()
            ->with(['reason', 'approver', 'appeal', 'exitPeriod', 'returnPeriod'])
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

        $periods = $this->lessonPeriods();

        return view('student.exit-permits.create', compact('student', 'reasons', 'periods'));
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

        // Pengecekan izin terbuka dan pembuatan izin berada dalam satu transaksi.
        // Tanpa itu dua pengajuan yang terkirim bersamaan bisa membuat dua izin
        // PENDING sekaligus, karena tabel ini tidak punya batasan yang mengunci
        // satu izin terbuka per siswa.
        $permit = DB::transaction(function () use ($student, $validated) {
            if ($this->hasOpenPermit($student->id)) {
                return null;
            }

            $exitPeriod = LessonPeriod::findOrFail($validated['exit_period_id']);
            $returnPeriod = LessonPeriod::findOrFail($validated['return_period_id']);

            $permit = ExitPermit::create([
                'student_id' => $student->id,
                'reason_id' => $validated['reason_id'],
                'reason_detail' => $validated['reason_detail'],
                'exit_period_id' => $exitPeriod->getKey(),
                'return_period_id' => $returnPeriod->getKey(),
                'requested_at' => now(),
                // Waktu diturunkan dari jam mulai jam pelajaran yang dipilih.
                // Nilai ini tetap disimpan karena timer, deteksi keterlambatan,
                // notifikasi, dan seluruh halaman modul BK membacanya.
                'planned_exit_at' => $this->plannedAt($exitPeriod),
                'planned_return_at' => $this->plannedAt($returnPeriod),
                'status' => ExitPermitStatus::Pending,
            ]);

            $this->audit('EXIT_PERMIT_REQUESTED', $permit, [
                'student_id' => $student->id,
                'reason_id' => $permit->reason_id,
                'exit_period' => $exitPeriod->displayLabel(),
                'return_period' => $returnPeriod->displayLabel(),
                'planned_return_at' => $permit->planned_return_at?->toDateTimeString(),
            ]);

            return $permit;
        });

        if ($permit === null) {
            return redirect()
                ->route('student.exit-permits.index')
                ->with('error', 'Anda masih memiliki izin yang menunggu persetujuan atau sedang aktif.');
        }

        return redirect()
            ->route('student.exit-permits.show', $permit)
            ->with('success', 'Pengajuan izin keluar terkirim. Menunggu persetujuan Guru BK.');
    }

    public function show(ExitPermit $permit): View
    {
        Gate::authorize('view', $permit);

        $permit->load(['reason', 'approver', 'appeal.decider', 'exitPeriod', 'returnPeriod']);

        return view('student.exit-permits.show', [
            'student' => Auth::user()->studentProfile,
            'permit' => $permit,
            'periodSummary' => $permit->periodSummary(),
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

        $created = DB::transaction(function () use ($permit, $request) {
            // Diperiksa ulang di dalam transaksi: dua klik cepat pada tombol
            // banding bisa sama-sama lolos pengecekan di atas sebelum keduanya
            // membuat baris, dan kolom exit_permit_id bersifat UNIQUE.
            if ($permit->appeal()->exists()) {
                return false;
            }

            $appeal = $permit->appeal()->create([
                'submitted_at' => now(),
                'reason' => $request->validated()['reason'],
            ]);

            $this->audit('EXIT_PERMIT_APPEAL_SUBMITTED', $appeal, [
                'exit_permit_id' => $permit->getKey(),
                'reason' => $appeal->reason,
            ]);

            return true;
        });

        if ($created === false) {
            return back()->with('error', 'Banding untuk izin ini sudah pernah diajukan.');
        }

        return redirect()
            ->route('student.exit-permits.show', $permit)
            ->with('success', 'Banding keterlambatan terkirim. Menunggu keputusan Guru BK.');
    }

    /**
     * Jam pelajaran reguler yang boleh dipilih. Jam istirahat tidak pernah
     * tampil sebagai pilihan.
     *
     * @return Collection<int, LessonPeriod>
     */
    private function lessonPeriods()
    {
        return LessonPeriod::regular()->get();
    }

    /**
     * Mengubah jam pelajaran menjadi tanggal hari ini pada jam mulainya.
     * Timezone diambil dari konfigurasi aplikasi, sehingga waktu yang
     * dibandingkan dengan now() selalu berada di kerangka waktu yang sama.
     */
    private function plannedAt(LessonPeriod $period): Carbon
    {
        return now()->setTimeFromTimeString($period->start_time);
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
