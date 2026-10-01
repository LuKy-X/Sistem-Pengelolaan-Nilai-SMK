<?php

namespace App\Http\Controllers\BK;

use App\Enums\AppealDecision;
use App\Enums\DisciplineCategoryType;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\Controller;
use App\Http\Requests\BK\DecideAppealRequest;
use App\Models\AcademicYear;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\ExitPermitAppeal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppealController extends Controller
{
    use HandlesDisciplinePoints, RecordsAuditTrail;

    public function index(Request $request): View
    {
        $decisionFilter = $request->query('decision');
        $search = trim((string) $request->query('q', ''));

        $baseQuery = ExitPermitAppeal::query()
            ->with(['exitPermit.student.currentEnrollment.schoolClass', 'exitPermit.reason', 'decider']);

        $decisionCounts = (clone $baseQuery)
            ->reorder()
            ->selectRaw('decision, COUNT(*) as total')
            ->groupBy('decision')
            ->pluck('total', 'decision');

        $appeals = $baseQuery
            ->when(in_array($decisionFilter, array_column(AppealDecision::cases(), 'value'), true),
                fn (Builder $query) => $query->where('decision', $decisionFilter))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('exitPermit.student', function (Builder $studentQuery) use ($search) {
                    $studentQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('submitted_at')
            ->paginate(10)
            ->withQueryString();

        $violationCategories = DisciplineCategory::query()
            ->where('is_active', true)
            ->where('type', DisciplineCategoryType::Violation->value)
            ->orderBy('name')
            ->get(['id', 'name', 'default_points']);

        return view('bk.appeals.index', [
            'appeals' => $appeals,
            'decisionCounts' => $decisionCounts,
            'decisionFilter' => $decisionFilter,
            'search' => $search,
            'violationCategories' => $violationCategories,
        ]);
    }

    public function decide(DecideAppealRequest $request, ExitPermitAppeal $appeal): RedirectResponse
    {
        if ($appeal->decision !== AppealDecision::Pending) {
            return back()->with('error', 'Banding ini sudah diputuskan sebelumnya.');
        }

        $validated = $request->validated();
        $decision = AppealDecision::from($validated['decision']);

        $permit = $appeal->exitPermit()->with('student')->firstOrFail();
        $category = isset($validated['sanction_category_id'])
            ? DisciplineCategory::find($validated['sanction_category_id'])
            : null;
        $sanctionPoints = $request->resolvedSanctionPoints($category);
        $recordSanction = $request->boolean('record_sanction') && $category !== null;
        $academicYearId = null;

        if ($recordSanction) {
            $academicYearId = $this->activeAcademicYear()?->id
                ?? AcademicYear::orderByDesc('start_date')->value('id');

            if ($academicYearId === null) {
                return back()->with('error', 'Belum ada tahun ajaran aktif. Hubungi admin untuk mengatur tahun ajaran.');
            }
        }

        DB::transaction(function () use ($appeal, $validated, $decision, $request, $permit, $category, $sanctionPoints, $recordSanction, $academicYearId) {
            $appeal->update([
                'decision' => $decision,
                'decision_note' => $validated['decision_note'] ?? null,
                'decided_by' => $request->user()?->staffProfile?->id,
                'decided_at' => now(),
            ]);

            $this->audit('EXIT_PERMIT_APPEAL_DECIDED', $appeal, [
                'decision' => $decision->value,
                'decision_note' => $appeal->decision_note,
            ]);

            if (! $recordSanction) {
                return;
            }

            DisciplineRecord::create([
                'student_id' => $permit->student_id,
                'academic_year_id' => $academicYearId,
                'category_id' => $category->id,
                'points_delta' => $sanctionPoints,
                'occurred_at' => $permit->actual_return_at ?? now(),
                'description' => "Sanksi atas banding keterlambatan yang ditolak: {$permit->reason_detail}",
                'source_type' => 'APPEAL',
                'source_id' => $appeal->getKey(),
                'created_by' => $request->user()?->getAuthIdentifier(),
            ]);

            $this->audit('APPEAL_SANCTION_RECORDED', $appeal, [
                'student_id' => $permit->student_id,
                'category_id' => $category->id,
                'points_delta' => $sanctionPoints,
            ]);
        });

        $message = $decision === AppealDecision::Accepted
            ? 'Alasan banding diterima. BK tidak menjatuhi sanksi atas keterlambatan ini.'
            : 'Alasan banding ditolak.';

        if ($recordSanction) {
            $message .= ' Sanksi telah dicatat pada poin kedisiplinan siswa.';
        }

        return redirect()
            ->route('counselor.appeals.index')
            ->with('success', $message);
    }
}
