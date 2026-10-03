<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\CareerCompany;
use App\Models\Department;
use App\Models\SiteStatistic;
use App\Models\StudentProfile;
use Closure;
use Illuminate\Support\Collection;
use Throwable;

/**
 * The headline figures shown on the public site.
 *
 * Administrators keep control of which counters appear, in which order and under
 * which label (`site_statistics`), but the number itself is always measured from
 * live school data: a counter typed by hand inevitably drifts from the roster, the
 * department list and the partner companies. A counter whose key is not measured
 * here keeps the value stored in the CMS.
 *
 * Every query goes through `guard()`, so a failing database degrades to the
 * configured value instead of breaking a page that must stay reachable.
 */
class SchoolStatisticsService
{
    /**
     * Database-measured counters used when the CMS has no hero statistics configured.
     *
     * @var list<array{key: string, label: string, suffix: string}>
     */
    private const FALLBACK_HERO_STATISTICS = [
        ['key' => 'siswa_aktif', 'label' => 'Siswa Aktif', 'suffix' => ''],
        ['key' => 'program_keahlian', 'label' => 'Program Keahlian', 'suffix' => ''],
        ['key' => 'mitra_industri', 'label' => 'Mitra Industri', 'suffix' => ''],
    ];

    /**
     * Measured value per statistic key, `null` when the key is not measured.
     *
     * @var array<string, float|int|null>
     */
    private array $measured = [];

    /**
     * Hero counters for the landing page (`site_statistics`, section = HERO).
     *
     * @return Collection<int, array{key: string, label: string, value: string, target: float, suffix: string, description: ?string}>
     */
    public function heroStatistics(): Collection
    {
        $configured = $this->configuredStatistics('HERO');

        if ($configured->isEmpty()) {
            return collect(self::FALLBACK_HERO_STATISTICS)
                ->map(fn (array $statistic): array => $this->shapeMeasured(
                    $statistic['key'],
                    $statistic['label'],
                    $statistic['suffix'],
                ))
                ->filter(fn (?array $statistic): bool => $statistic !== null)
                ->values();
        }

        return $configured
            // Current occupation is not enough evidence to claim a graduate has been absorbed into employment.
            ->reject(fn (SiteStatistic $statistic): bool => $statistic->key === 'lulusan_terserap')
            ->map(fn (SiteStatistic $statistic): array => $this->shape($statistic))
            ->values();
    }

    /**
     * Every active counter, whatever section it belongs to.
     *
     * @return Collection<int, array{key: string, label: string, value: string, target: float, suffix: string, description: ?string}>
     */
    public function activeStatistics(): Collection
    {
        return $this->configuredStatistics()
            ->map(fn (SiteStatistic $statistic): array => $this->shape($statistic))
            ->values();
    }

    /**
     * @return Collection<int, SiteStatistic>
     */
    private function configuredStatistics(?string $section = null): Collection
    {
        try {
            return SiteStatistic::query()
                ->where('is_active', true)
                ->when($section !== null, fn ($query) => $query->where('section', $section))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } catch (Throwable) {
            return new Collection;
        }
    }

    /**
     * @return array{key: string, label: string, value: string, target: float, suffix: string, description: ?string}
     */
    private function shape(SiteStatistic $statistic): array
    {
        $measured = $this->measure($statistic->key);

        if ($measured === null) {
            return [
                'key' => $statistic->key,
                'label' => $statistic->label,
                'value' => trim((string) $statistic->value),
                'target' => (float) preg_replace('/\D/', '', (string) $statistic->value),
                'suffix' => $this->suffixOf((string) $statistic->value),
                'description' => $statistic->description,
            ];
        }

        return [
            'key' => $statistic->key,
            'label' => $statistic->label,
            'value' => number_format((float) $measured, 0, ',', '.'),
            'target' => (float) $measured,
            'suffix' => $this->measuredSuffix($statistic->key),
            'description' => $statistic->description,
        ];
    }

    /**
     * @return array{key: string, label: string, value: string, target: float, suffix: string, description: ?string}
     */
    private function shapeMeasured(string $key, string $label, string $suffix): ?array
    {
        $measured = $this->measure($key);

        if ($measured === null) {
            return null;
        }

        return [
            'key' => $key,
            'label' => $label,
            'value' => number_format((float) $measured, 0, ',', '.'),
            'target' => (float) $measured,
            'suffix' => $suffix,
            'description' => null,
        ];
    }

    /**
     * Measure a statistic from live data, or null when the key is unknown.
     */
    private function measure(string $key): float|int|null
    {
        if (array_key_exists($key, $this->measured)) {
            return $this->measured[$key];
        }

        return $this->measured[$key] = match ($key) {
            'siswa_aktif' => $this->guard(fn (): int => StudentProfile::query()
                ->where('status', 'ACTIVE')
                ->count()),
            'program_keahlian' => $this->guard(fn (): int => Department::query()
                ->where('is_active', true)
                ->count()),
            'mitra_industri' => $this->guard(fn (): int => CareerCompany::query()->count()),
            'lulusan_terserap' => $this->guard(fn (): float => $this->employedGraduatePercentage()),
            default => null,
        };
    }

    /**
     * Share of tracked graduates that are working, self-employed or studying.
     */
    private function employedGraduatePercentage(): float
    {
        $total = AlumniProfile::query()->count();

        if ($total === 0) {
            return 0.0;
        }

        $employed = AlumniProfile::query()
            ->whereNotNull('current_occupation')
            ->where('current_occupation', '!=', '')
            ->count();

        return round($employed / $total * 100);
    }

    /**
     * Symbols that belong to a measured counter.
     *
     * A measured counter shows an exact number, so the symbol comes from the
     * measurement itself (`%` for a percentage) rather than from the configured
     * value, whose `+` and digits describe a hand-typed figure.
     */
    private function measuredSuffix(string $key): string
    {
        return $key === 'lulusan_terserap' ? '%' : '';
    }

    /**
     * Symbols the counter keeps while counting, such as `%` or `+`. Digit
     * separators (`1.290`) are formatting, not a suffix.
     */
    private function suffixOf(string $value): string
    {
        return (string) preg_replace('/[\d\s.,]/u', '', $value);
    }

    /**
     * Run a measurement, turning a database failure into `null` so the caller can
     * fall back to the value configured in the CMS.
     */
    private function guard(Closure $measurement): float|int|null
    {
        try {
            return $measurement();
        } catch (Throwable) {
            return null;
        }
    }
}
