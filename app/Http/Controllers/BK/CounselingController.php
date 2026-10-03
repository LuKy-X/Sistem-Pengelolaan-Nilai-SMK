<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\BK\Concerns\ResolvesCounselorClasses;
use App\Http\Controllers\Controller;
use App\Http\Requests\BK\StoreCounselingLogRequest;
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

class CounselingController extends Controller
{
    use HandlesDisciplinePoints, RecordsAuditTrail, ResolvesCounselorClasses;

    /**
     * @var list<string>
     */
    protected const COUNSELING_SOURCES = [
        'COUNSELING',
        'INTERVIEW',
        'HOME_VISIT',
        'PARENT_MEETING',
    ];

    /**
     * Label layanan konseling untuk ditampilkan pada form dan filter.
     *
     * @var array<string, string>
     */
    protected const COUNSELING_LABELS = [
        'COUNSELING' => 'Konseling Individual',
        'INTERVIEW' => 'Wawancara Siswa',
        'HOME_VISIT' => 'Kunjungan Rumah',
        'PARENT_MEETING' => 'Pertemuan Orang Tua',
    ];

    public function index(Request $request): View
    {
        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;

        $counselorClasses = $this->counselorClasses();
        $counselorClassIds = $counselorClasses->pluck('id')->all();

        $classIdFilter = $request->integer('class_id') ?: null;
        $studentIdFilter = $request->integer('student_id') ?: null;
        $serviceTypeFilter = in_array($request->query('service_type'), self::COUNSELING_SOURCES, true)
            ? $request->query('service_type')
            : null;
        $search = trim((string) $request->query('q', ''));

        // Daftar siswa dibatasi oleh kelas binaan BK. Filter kelas hanya mempersempit
        // cakupan, tidak boleh memperluas ke kelas di luar binaan BK.
        $allowedClassIds = $classIdFilter !== null && in_array($classIdFilter, $counselorClassIds, true)
            ? [$classIdFilter]
            : $counselorClassIds;

        $students = StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->where('status', 'ACTIVE')
            ->whereHas('currentEnrollment', fn (Builder $query) => $query
                ->whereIn('class_id', $allowedClassIds === [] ? [0] : $allowedClassIds))
            ->orderBy('full_name')
            ->get();

        $studentIds = $students->pluck('id')->all();

        $logs = DisciplineRecord::query()
            ->with(['student.currentEnrollment.schoolClass', 'category', 'creator'])
            ->whereIn('source_type', self::COUNSELING_SOURCES)
            ->whereIn('student_id', $studentIds === [] ? [0] : $studentIds)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->when($studentIdFilter !== null, fn (Builder $query) => $query->where('student_id', $studentIdFilter))
            ->when($serviceTypeFilter !== null, fn (Builder $query) => $query->where('source_type', $serviceTypeFilter))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhereHas('student', fn (Builder $studentQuery) => $studentQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $serviceCounts = DisciplineRecord::query()
            ->whereIn('source_type', self::COUNSELING_SOURCES)
            ->whereIn('student_id', $studentIds === [] ? [0] : $studentIds)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->reorder()
            ->selectRaw('source_type, COUNT(*) as total')
            ->groupBy('source_type')
            ->pluck('total', 'source_type');

        $studentsWithLogsCount = DisciplineRecord::query()
            ->whereIn('source_type', self::COUNSELING_SOURCES)
            ->whereIn('student_id', $studentIds === [] ? [0] : $studentIds)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->distinct()
            ->count('student_id');

        return view('bk.counseling.index', [
            'logs' => $logs,
            'students' => $students,
            'counselorClasses' => $counselorClasses,
            'categories' => $this->categoryOptions(),
            'academicYear' => $academicYear,
            'serviceCounts' => $serviceCounts,
            'serviceTypes' => self::COUNSELING_LABELS,
            'totalLogs' => (int) $serviceCounts->sum(),
            'studentsWithLogs' => (int) $studentsWithLogsCount,
            'studentIdFilter' => $studentIdFilter,
            'classIdFilter' => $classIdFilter,
            'serviceTypeFilter' => $serviceTypeFilter,
            'search' => $search,
        ]);
    }

    public function store(StoreCounselingLogRequest $request): RedirectResponse
    {
        Gate::authorize('create', DisciplineRecord::class);

        $academicYear = $this->activeAcademicYear();

        if ($academicYear === null) {
            return back()->with('error', 'Belum ada tahun ajaran aktif. Hubungi admin untuk mengatur tahun ajaran.');
        }

        $validated = $request->validated();

        $log = DB::transaction(function () use ($validated, $academicYear, $request) {
            $log = DisciplineRecord::create([
                'student_id' => $validated['student_id'],
                'academic_year_id' => $academicYear->id,
                'category_id' => $validated['category_id'],
                'points_delta' => 0,
                'occurred_at' => $validated['occurred_at'],
                'description' => $validated['summary'],
                'source_type' => $validated['service_type'],
                'created_by' => $request->user()->getAuthIdentifier(),
            ]);

            $this->audit('COUNSELING_LOG_CREATED', $log, [
                'student_id' => $log->student_id,
                'service_type' => $log->source_type,
            ]);

            return $log;
        });

        return redirect()
            ->route('counselor.counseling.index', ['student_id' => $log->student_id])
            ->with('success', 'Rekam jejak konseling tersimpan tanpa memengaruhi saldo poin siswa.');
    }

    /**
     * @return Collection<int, DisciplineCategory>
     */
    protected function categoryOptions(): Collection
    {
        return DisciplineCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
