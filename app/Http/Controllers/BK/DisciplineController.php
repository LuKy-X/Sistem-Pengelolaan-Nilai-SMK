<?php

namespace App\Http\Controllers\BK;

use App\Enums\DisciplineCategoryType;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\BK\Concerns\ResolvesCounselorClasses;
use App\Http\Controllers\Controller;
use App\Http\Requests\BK\StoreDisciplineRecordRequest;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    use HandlesDisciplinePoints, RecordsAuditTrail, ResolvesCounselorClasses;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DisciplineRecord::class);

        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;
        $setting = $this->disciplineSetting($academicYearId);

        $counselorStudentIds = $this->counselorClassIds() === []
            ? []
            : $this->counselorStudentOptions()->pluck('id')->all();

        $filters = [
            'student_id' => $request->integer('student_id') ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'type' => $request->query('type'),
            'source_type' => $request->query('source_type'),
            'date_from' => $this->safeDateQuery($request->query('date_from')),
            'date_to' => $this->safeDateQuery($request->query('date_to')),
            'q' => trim((string) $request->query('q', '')),
        ];

        $recordsQuery = DisciplineRecord::query()
            ->with(['student.currentEnrollment.schoolClass', 'category', 'creator'])
            ->whereIn('student_id', $counselorStudentIds === [] ? [0] : $counselorStudentIds);

        $recordsQuery
            ->when($filters['student_id'], fn (Builder $query, int $studentId) => $query->where('student_id', $studentId))
            ->when($filters['category_id'], fn (Builder $query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when(in_array($filters['type'], array_column(DisciplineCategoryType::cases(), 'value'), true),
                fn (Builder $query) => $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('type', $filters['type'])))
            ->when($filters['source_type'], fn (Builder $query, string $sourceType) => $query->where('source_type', $sourceType))
            ->when($filters['date_from'], fn (Builder $query) => $query->whereDate('occurred_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn (Builder $query) => $query->whereDate('occurred_at', '<=', $filters['date_to']))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $query->where(function (Builder $inner) use ($filters) {
                    $inner->where('description', 'like', "%{$filters['q']}%")
                        ->orWhereHas('student', fn (Builder $studentQuery) => $studentQuery
                            ->where('full_name', 'like', "%{$filters['q']}%")
                            ->orWhere('nis', 'like', "%{$filters['q']}%"));
                });
            });

        $records = $recordsQuery
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $yearRecords = DisciplineRecord::query()
            ->whereIn('student_id', $counselorStudentIds === [] ? [0] : $counselorStudentIds)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId));

        $totals = (clone $yearRecords)
            ->selectRaw('COUNT(*) as total_records, COALESCE(SUM(CASE WHEN points_delta < 0 THEN points_delta ELSE 0 END), 0) as total_violation_points, COALESCE(SUM(CASE WHEN points_delta > 0 THEN points_delta ELSE 0 END), 0) as total_reward_points')
            ->first();

        $balances = $this->pointBalanceMap($counselorStudentIds, $setting, $academicYearId);
        $thresholdCounts = $this->countStudentsReaching($balances, $setting);

        $balanceStudents = StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->whereIn('id', $balances === [] ? [0] : array_keys($balances))
            ->get()
            ->sortBy(fn (StudentProfile $student) => $balances[$student->id])
            ->take(10)
            ->values()
            ->map(fn (StudentProfile $student) => [
                'student' => $student,
                'balance' => $balances[$student->id],
                'standing' => $this->disciplineStanding($balances[$student->id], $setting),
            ]);

        return view('bk.discipline.index', [
            'records' => $records,
            'students' => $this->studentOptions(),
            'counselorClasses' => $this->counselorClasses(),
            'categories' => $this->categoryOptions(),
            'balanceStudents' => $balanceStudents,
            'setting' => $setting,
            'academicYear' => $academicYear,
            'totalRecords' => (int) ($totals?->total_records ?? 0),
            'totalViolationPoints' => abs((int) ($totals?->total_violation_points ?? 0)),
            'totalRewardPoints' => (int) ($totals?->total_reward_points ?? 0),
            'thresholdCounts' => $thresholdCounts,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', DisciplineRecord::class);

        return view('bk.discipline.create', [
            'students' => $this->studentOptions(),
            'categories' => $this->categoryOptions(),
            'academicYear' => $this->activeAcademicYear(),
            'prefill' => [
                'student_id' => $request->integer('student_id') ?: null,
                'category_id' => $request->integer('category_id') ?: null,
                'source_type' => $request->query('source_type', 'MANUAL'),
                'occurred_at' => $request->date('occurred_at')?->toDateString() ?? now()->toDateString(),
            ],
        ]);
    }

    public function store(StoreDisciplineRecordRequest $request): RedirectResponse
    {
        $academicYear = $this->activeAcademicYear();

        if ($academicYear === null) {
            return back()->with('error', 'Belum ada tahun ajaran aktif. Hubungi admin untuk mengatur tahun ajaran.');
        }

        $validated = $request->validated();

        $record = DB::transaction(function () use ($validated, $academicYear, $request) {
            $record = DisciplineRecord::create([
                'student_id' => $validated['student_id'],
                'academic_year_id' => $academicYear->id,
                'category_id' => $validated['category_id'],
                'points_delta' => $validated['points_delta'],
                'occurred_at' => $validated['occurred_at'],
                'description' => $validated['description'],
                'source_type' => $validated['source_type'] ?? 'MANUAL',
                'created_by' => $request->user()->getAuthIdentifier(),
            ]);

            $this->audit('DISCIPLINE_RECORD_CREATED', $record, [
                'student_id' => $record->student_id,
                'category_id' => $record->category_id,
                'points_delta' => $record->points_delta,
            ]);

            return $record;
        });

        $setting = $this->disciplineSetting($academicYear->id);
        $balance = $this->pointBalance($record->student_id, $setting, $academicYear->id);
        $suggestedType = $this->suggestedLetterType($balance, $setting);

        $message = sprintf(
            'Catatan %s tersimpan. Saldo poin siswa kini %d.',
            $record->load('category')->category?->name ?? 'kedisiplinan',
            $balance,
        );

        if ($suggestedType !== null) {
            $message .= " Poin siswa sudah menyentuh ambang batas, silakan terbitkan {$suggestedType->value}.";
        }

        return redirect()
            ->route('counselor.discipline.index', ['student_id' => $record->student_id])
            ->with('success', $message);
    }

    public function destroy(DisciplineRecord $record): RedirectResponse
    {
        Gate::authorize('delete', $record);
        $this->authorizeCounselorStudent(
            $record->student_id,
            'Anda tidak memiliki akses ke catatan kedisiplinan siswa ini.'
        );

        $studentId = $record->student_id;
        $description = $record->description;

        $this->audit('DISCIPLINE_RECORD_DELETED', $record, [], [
            'student_id' => $studentId,
            'description' => $description,
        ]);

        $record->delete();

        return redirect()
            ->route('counselor.discipline.index', ['student_id' => $studentId])
            ->with('success', 'Catatan kedisiplinan berhasil dihapus dan saldo poin siswa dikoreksi.');
    }

    /**
     * @return Collection<int, StudentProfile>
     */
    protected function studentOptions(): Collection
    {
        return $this->counselorStudentOptions();
    }

    /**
     * @return Collection<int, DisciplineCategory>
     */
    protected function categoryOptions(): Collection
    {
        return DisciplineCategory::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }
}
