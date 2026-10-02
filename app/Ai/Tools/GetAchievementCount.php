<?php

namespace App\Ai\Tools;

use App\Models\Achievement;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Counts school achievements with optional filters.
 */
class GetAchievementCount implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Menghitung total prestasi sekolah. Bisa difilter berdasarkan tingkat atau lingkup. Juga bisa mengelompokkan berdasarkan tingkat atau tahun.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $levelSearch = $request['level'] ?? null;
        $groupByLevel = filter_var($request['group_by_level'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $groupByYear = filter_var($request['group_by_year'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        if ($groupByLevel) {
            $results = Achievement::query()
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->orderByDesc('count')
                ->get();

            return json_encode([
                'by_level' => $results->map(fn ($r) => [
                    'level' => $r->level ?: 'Tidak disebutkan',
                    'count' => (int) $r->count,
                ])->values()->all(),
                'total' => $results->sum('count'),
            ]);
        }

        if ($groupByYear) {
            $results = Achievement::query()
                ->selectRaw('YEAR(achievement_date) as year, COUNT(*) as count')
                ->whereNotNull('achievement_date')
                ->groupBy('year')
                ->orderByDesc('year')
                ->limit(5)
                ->get();

            return json_encode([
                'by_year' => $results->map(fn ($r) => [
                    'year' => $r->year,
                    'count' => (int) $r->count,
                ])->values()->all(),
                'total' => Achievement::query()->count(),
            ]);
        }

        $query = Achievement::query();

        if ($levelSearch) {
            $query->where('level', 'like', "%{$levelSearch}%");
        }

        return json_encode([
            'count' => $query->count(),
            'filter' => $levelSearch ? "tingkat {$levelSearch}" : null,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'level' => $schema->string()
                ->description('Filter berdasarkan tingkat prestasi. Contoh: "nasional", "provinsi".'),
            'group_by_level' => $schema->boolean()
                ->description('Kelompokkan hasil berdasarkan tingkat. Default: false.')
                ->default(false),
            'group_by_year' => $schema->boolean()
                ->description('Kelompokkan hasil berdasarkan tahun. Default: false.')
                ->default(false),
        ];
    }
}
