<?php

namespace App\Http\Controllers\BK;

use App\Enums\DisciplinaryLetterType;
use App\Enums\ExitPermitStatus;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\ResolvesCounselorClasses;
use App\Http\Controllers\Controller;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineRecord;
use App\Models\ExitPermit;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StudentController extends Controller
{
    use HandlesDisciplinePoints, ResolvesCounselorClasses;

    /**
     * Cakupan daftar siswa yang bisa dipilih lewat KPI card.
     *
     * @var list<string>
     */
    public const SCOPES = ['all', 'attention', 'sp1', 'sp23'];

    public function index(Request $request): View
    {
        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;
        $setting = $this->disciplineSetting($academicYearId);

        $search = trim((string) $request->query('q', ''));
        $scope = in_array($request->query('scope'), self::SCOPES, true)
            ? $request->query('scope')
            : 'all';

        $studentsQuery = StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->where('status', 'ACTIVE')
            ->when($search !== '', fn (Builder $query) => $query
                ->where(fn (Builder $inner) => $inner
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")))
            ->orderBy('full_name');

        $activeStudentIds = StudentProfile::query()->where('status', 'ACTIVE')->pluck('id');

        $allBalances = $this->pointBalanceMap($activeStudentIds->all(), $setting, $academicYearId);

        $thresholdCounts = $this->countStudentsReaching($allBalances, $setting);

        if ($scope !== 'all') {
            $allowedTones = match ($scope) {
                'attention' => ['watch', 'warning', 'high', 'critical'],
                'sp1' => ['warning'],
                'sp23' => ['high', 'critical'],
                default => [],
            };

            $scopedIds = array_values(array_filter(
                array_keys($allBalances),
                fn (int $studentId) => in_array(
                    $this->disciplineStanding($allBalances[$studentId], $setting)['tone'],
                    $allowedTones,
                    true
                )
            ));

            $studentsQuery->whereIn('id', $scopedIds === [] ? [0] : $scopedIds);
        }

        $students = $studentsQuery->paginate(12)->withQueryString();
        $balances = array_intersect_key($allBalances, $students->getCollection()->keyBy('id')->all());

        $standings = $students->getCollection()
            ->mapWithKeys(fn (StudentProfile $student) => [
                $student->id => $this->disciplineStanding($balances[$student->id] ?? 0, $setting),
            ]);

        return view('bk.students.index', [
            'students' => $students,
            'balances' => $balances,
            'standings' => $standings,
            'setting' => $setting,
            'academicYear' => $academicYear,
            'search' => $search,
            'scope' => $scope,
            'scopes' => self::SCOPES,
            'thresholdCounts' => $thresholdCounts,
            'totalStudents' => $activeStudentIds->count(),
        ]);
    }

    public function show(StudentProfile $student): View
    {
        $this->authorizeCounselorStudent($student);

        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;
        $setting = $this->disciplineSetting($academicYearId);

        $student->load('currentEnrollment.schoolClass.department');

        $balance = $this->pointBalance($student->id, $setting, $academicYearId);

        $records = DisciplineRecord::query()
            ->with('category')
            ->where('student_id', $student->id)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        $counselingLogs = $records
            ->whereIn('source_type', ['COUNSELING', 'INTERVIEW', 'HOME_VISIT', 'PARENT_MEETING'])
            ->take(10)
            ->values();

        $exitPermits = ExitPermit::query()
            ->with(['reason', 'appeal'])
            ->where('student_id', $student->id)
            ->orderByDesc('requested_at')
            ->limit(10)
            ->get();

        $letters = DisciplinaryLetter::query()
            ->with('issuer')
            ->where('student_id', $student->id)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->orderByDesc('issued_at')
            ->get();

        $violationPoints = (int) $records
            ->where('points_delta', '<', 0)
            ->sum('points_delta');

        $rewardPoints = (int) $records->where('points_delta', '>', 0)->sum('points_delta');

        return view('bk.students.show', [
            'student' => $student,
            'balance' => $balance,
            'standing' => $this->disciplineStanding($balance, $setting),
            'setting' => $setting,
            'academicYear' => $academicYear,
            'records' => $records,
            'counselingLogs' => $counselingLogs,
            'exitPermits' => $exitPermits,
            'letters' => $letters,
            'violationPoints' => abs($violationPoints),
            'rewardPoints' => $rewardPoints,
            'suggestedLetter' => $this->suggestedLetterType($balance, $setting),
            'permitStats' => $this->permitStats($student->id),
            'letterSummary' => $this->letterSummary($letters),
        ]);
    }

    /**
     * Statistik izin keluar siswa dihitung terpisah agar angka total tidak terbatas pada 10 baris terakhir.
     *
     * @return array{total: int, late: int, rejected: int, pending: int, active: int}
     */
    protected function permitStats(int $studentId): array
    {
        $counts = ExitPermit::query()
            ->where('student_id', $studentId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $active = ExitPermit::query()
            ->where('student_id', $studentId)
            ->whereIn('status', [ExitPermitStatus::Approved->value, ExitPermitStatus::Late->value])
            ->whereNull('actual_return_at')
            ->count();

        return [
            'total' => (int) $counts->sum(),
            'late' => (int) ($counts[ExitPermitStatus::Late->value] ?? 0),
            'rejected' => (int) ($counts[ExitPermitStatus::Rejected->value] ?? 0),
            'pending' => (int) ($counts[ExitPermitStatus::Pending->value] ?? 0),
            'active' => $active,
        ];
    }

    /**
     * @param  Collection<int, DisciplinaryLetter>  $letters
     * @return array<string, int>
     */
    protected function letterSummary(Collection $letters): array
    {
        $summary = [
            DisciplinaryLetterType::Sp1->value => 0,
            DisciplinaryLetterType::Sp2->value => 0,
            DisciplinaryLetterType::Sp3->value => 0,
        ];

        foreach ($letters as $letter) {
            $summary[$letter->type->value] = ($summary[$letter->type->value] ?? 0) + 1;
        }

        return $summary;
    }
}
