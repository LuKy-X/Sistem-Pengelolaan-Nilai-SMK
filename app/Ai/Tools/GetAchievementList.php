<?php

namespace App\Ai\Tools;

use App\Models\Achievement;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves school achievements and awards.
 */
class GetAchievementList implements Tool
{
    private const MAX_RESULTS = 15;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil daftar prestasi sekolah (juara lomba, penghargaan, dll). Bisa difilter berdasarkan tingkat (nasional/provinsi/kota), lingkup, atau tahun.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $levelSearch = $request['level'] ?? null;
        $scopeSearch = $request['scope'] ?? null;
        $yearSearch = $request['year'] ?? null;
        $featuredOnly = filter_var($request['featured_only'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        $query = Achievement::query()
            ->with('category:id,name')
            ->orderByDesc('achievement_date')
            ->limit(self::MAX_RESULTS);

        if ($levelSearch) {
            $query->where('level', 'like', "%{$levelSearch}%");
        }

        if ($scopeSearch) {
            $query->where('scope', 'like', "%{$scopeSearch}%");
        }

        if ($yearSearch) {
            $query->whereYear('achievement_date', $yearSearch);
        }

        if ($featuredOnly) {
            $query->where('is_featured', true);
        }

        $achievements = $query->get();

        return json_encode([
            'achievements' => $achievements->map(fn ($a) => [
                'title' => $a->title,
                'category' => $a->category?->name,
                'scope' => $a->scope,
                'level' => $a->level,
                'rank' => $a->rank,
                'organizer' => $a->organizer,
                'date' => $a->achievement_date?->format('d F Y'),
            ])->values()->all(),
            'total' => $achievements->count(),
            'note' => $achievements->count() >= self::MAX_RESULTS ? 'Menampilkan '.self::MAX_RESULTS.' prestasi terbaru.' : null,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'level' => $schema->string()
                ->description('Tingkat prestasi. Contoh: "nasional", "provinsi", "kota", "kabupaten".'),
            'scope' => $schema->string()
                ->description('Lingkup prestasi. Contoh: "LKS", "olimpiade", "olahraga".'),
            'year' => $schema->integer()
                ->description('Tahun prestasi. Contoh: 2024, 2025.'),
            'featured_only' => $schema->boolean()
                ->description('Hanya tampilkan prestasi unggulan. Default: false.')
                ->default(false),
        ];
    }
}
