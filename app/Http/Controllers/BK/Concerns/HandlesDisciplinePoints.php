<?php

namespace App\Http\Controllers\BK\Concerns;

use App\Enums\DisciplinaryLetterType;
use App\Models\AcademicYear;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;

trait HandlesDisciplinePoints
{
    /**
     * Tahun ajaran aktif; fallback ke tahun ajaran terakhir bila belum ada yang ditandai aktif.
     */
    protected function activeAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_active', true)
            ->orderByDesc('start_date')
            ->first()
            ?? AcademicYear::orderByDesc('start_date')
                ->latest('id')
                ->first();
    }

    /**
     * Tanggal dari query string yang aman dipakai sebagai filter; null bila kosong
     * atau formatnya bukan Y-m-d yang valid.
     */
    protected function safeDateQuery(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return null;
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])
            ? $value
            : null;
    }

    protected function disciplineSetting(?int $academicYearId): ?DisciplineSetting
    {
        if ($academicYearId === null) {
            return null;
        }

        return DisciplineSetting::where('academic_year_id', $academicYearId)->first();
    }

    /**
     * Hitung saldo poin banyak siswa dalam satu query (initial_points + SUM(points_delta), dibatasi minimum_points).
     *
     * @param  array<int>  $studentIds
     * @return array<int, int>
     */
    protected function pointBalanceMap(array $studentIds, ?DisciplineSetting $setting, ?int $academicYearId): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        if ($studentIds === []) {
            return [];
        }

        $initialPoints = $setting?->initial_points ?? 100;
        $minimumPoints = $setting?->minimum_points ?? 0;

        $deltas = DisciplineRecord::query()
            ->whereIn('student_id', $studentIds)
            ->when($academicYearId !== null, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->groupBy('student_id')
            ->selectRaw('student_id, COALESCE(SUM(points_delta), 0) as total_delta')
            ->pluck('total_delta', 'student_id');

        $balances = [];

        foreach ($studentIds as $studentId) {
            $balances[$studentId] = max($minimumPoints, $initialPoints + (int) $deltas->get($studentId, 0));
        }

        return $balances;
    }

    protected function pointBalance(int $studentId, ?DisciplineSetting $setting, ?int $academicYearId): int
    {
        return $this->pointBalanceMap([$studentId], $setting, $academicYearId)[$studentId];
    }

    /**
     * Klasifikasi saldo poin terhadap ambang batas SP pada tahun ajaran berjalan.
     *
     * @return array{label: string, badge: string, tone: string}
     */
    protected function disciplineStanding(int $balance, ?DisciplineSetting $setting): array
    {
        if ($this->reachedThreshold($balance, $setting?->sp3_threshold)) {
            return ['label' => 'Kritis', 'badge' => 'badge-red', 'tone' => 'critical'];
        }

        if ($this->reachedThreshold($balance, $setting?->sp2_threshold)) {
            return ['label' => 'Tinggi', 'badge' => 'badge-red', 'tone' => 'high'];
        }

        if ($this->reachedThreshold($balance, $setting?->sp1_threshold)) {
            return ['label' => 'Waspada', 'badge' => 'badge-yellow', 'tone' => 'warning'];
        }

        if ($this->reachedThreshold($balance, $setting?->warning_threshold)) {
            return ['label' => 'Perlu Perhatian', 'badge' => 'badge-yellow', 'tone' => 'watch'];
        }

        return ['label' => 'Aman', 'badge' => 'badge-green', 'tone' => 'safe'];
    }

    /**
     * Saran jenis SP otomatis berdasarkan saldo poin siswa.
     */
    protected function suggestedLetterType(int $balance, ?DisciplineSetting $setting): ?DisciplinaryLetterType
    {
        if ($this->reachedThreshold($balance, $setting?->sp3_threshold)) {
            return DisciplinaryLetterType::Sp3;
        }

        if ($this->reachedThreshold($balance, $setting?->sp2_threshold)) {
            return DisciplinaryLetterType::Sp2;
        }

        if ($this->reachedThreshold($balance, $setting?->sp1_threshold)) {
            return DisciplinaryLetterType::Sp1;
        }

        return null;
    }

    /**
     * @param  array<int, int>  $balances  daftar saldo poin siswa
     * @return array<string, int> jumlah siswa per ambang batas yang terlampaui
     */
    protected function countStudentsReaching(array $balances, ?DisciplineSetting $setting): array
    {
        $counts = [
            'sp1' => 0,
            'sp2' => 0,
            'sp3' => 0,
            'warning' => 0,
        ];

        foreach ($balances as $balance) {
            if ($this->reachedThreshold($balance, $setting?->sp3_threshold)) {
                $counts['sp3']++;
            } elseif ($this->reachedThreshold($balance, $setting?->sp2_threshold)) {
                $counts['sp2']++;
            } elseif ($this->reachedThreshold($balance, $setting?->sp1_threshold)) {
                $counts['sp1']++;
            } elseif ($this->reachedThreshold($balance, $setting?->warning_threshold)) {
                $counts['warning']++;
            }
        }

        return $counts;
    }

    protected function reachedThreshold(int $balance, ?int $threshold): bool
    {
        return $threshold !== null && $balance <= $threshold;
    }
}
