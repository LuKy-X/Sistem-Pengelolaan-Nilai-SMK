<?php

namespace App\Http\Controllers\BK;

use App\Enums\AppealDecision;
use App\Enums\DisciplinaryLetterType;
use App\Enums\DisciplineCategoryType;
use App\Enums\ExitPermitStatus;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\Controller;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use HandlesDisciplinePoints;

    public function index(): View
    {
        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;
        $setting = $this->disciplineSetting($academicYearId);

        $studentsOutCount = ExitPermit::query()
            ->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
            ->whereNull('actual_return_at')
            ->count();

        $pendingPermitCount = ExitPermit::query()
            ->where('status', ExitPermitStatus::Pending->value)
            ->count();

        $pendingAppealCount = ExitPermitAppeal::query()
            ->where('decision', AppealDecision::Pending->value)
            ->count();

        $violationThisMonth = DisciplineRecord::query()
            ->whereHas('category', fn (Builder $query) => $query->where('type', DisciplineCategoryType::Violation->value))
            ->whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->count();

        $pendingPermits = ExitPermit::query()
            ->with(['student.currentEnrollment.schoolClass', 'reason'])
            ->where('status', ExitPermitStatus::Pending->value)
            ->oldest('requested_at')
            ->limit(5)
            ->get();

        $trackedStudentCount = DisciplineRecord::query()
            ->whereHas('student')
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->distinct()
            ->count('student_id');

        $letterCounts = DisciplinaryLetter::query()
            ->where('status', 'ACTIVE')
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('bk.dashboard.index', [
            'academicYear' => $academicYear,
            'setting' => $setting,
            'studentsOutCount' => $studentsOutCount,
            'pendingPermitCount' => $pendingPermitCount,
            'pendingAppealCount' => $pendingAppealCount,
            'violationThisMonth' => $violationThisMonth,
            'trackedStudentCount' => $trackedStudentCount,
            'activeLetterCount' => (int) $letterCounts->sum(),
            'sp1Count' => (int) ($letterCounts[DisciplinaryLetterType::Sp1->value] ?? 0),
            'sp2Count' => (int) ($letterCounts[DisciplinaryLetterType::Sp2->value] ?? 0),
            'sp3Count' => (int) ($letterCounts[DisciplinaryLetterType::Sp3->value] ?? 0),
            'pendingPermits' => $pendingPermits,
            'topViolators' => $this->topViolators($setting, $academicYearId, 6),
            'permitChart' => $this->permitTrendChart(),
            'violationChart' => $this->violationCategoryChart($academicYearId),
        ]);
    }

    /**
     * Siswa dengan akumulasi poin terendah, yaitu yang paling memerlukan perhatian BK.
     *
     * @return Collection<int, array{student: StudentProfile, balance: int, standing: array{label: string, badge: string, tone: string}}>
     */
    protected function topViolators(?DisciplineSetting $setting, ?int $academicYearId, int $limit): Collection
    {
        $rows = DisciplineRecord::query()
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(points_delta) as total_delta')
            ->orderBy('total_delta')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $students = StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->whereIn('id', $rows->pluck('student_id'))
            ->get()
            ->keyBy('id');

        $initialPoints = $setting?->initial_points ?? 100;
        $minimumPoints = $setting?->minimum_points ?? 0;

        return $rows->map(function (object $row) use ($students, $initialPoints, $minimumPoints, $setting) {
            $student = $students->get($row->student_id);

            if (! $student instanceof StudentProfile) {
                return null;
            }

            $balance = max($minimumPoints, $initialPoints + (int) $row->total_delta);

            return [
                'student' => $student,
                'balance' => $balance,
                'standing' => $this->disciplineStanding($balance, $setting),
            ];
        })->filter()->values();
    }

    /**
     * @return array{labels: array<int, string>, totals: array<int, int>}
     */
    protected function permitTrendChart(): array
    {
        $start = now()->subDays(6)->startOfDay();

        $totals = ExitPermit::query()
            ->where('requested_at', '>=', $start)
            ->selectRaw('DATE(requested_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $series = [];

        for ($day = $start->copy(); $day->lte(now()); $day = $day->addDay()) {
            $labels[] = $day->translatedFormat('D');
            $series[] = (int) ($totals[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'totals' => $series];
    }

    /**
     * @return array{labels: array<int, string>, totals: array<int, int>}
     */
    protected function violationCategoryChart(?int $academicYearId): array
    {
        $rows = DisciplineRecord::query()
            ->whereHas('category', fn (Builder $query) => $query->where('type', DisciplineCategoryType::Violation->value))
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->join('discipline_categories', 'discipline_records.category_id', '=', 'discipline_categories.id')
            ->groupBy('discipline_categories.id', 'discipline_categories.name')
            ->orderByDesc('total')
            ->selectRaw('discipline_categories.name as name, COUNT(*) as total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'totals' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
        ];
    }
}
