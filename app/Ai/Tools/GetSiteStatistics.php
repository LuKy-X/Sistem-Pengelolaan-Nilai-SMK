<?php

namespace App\Ai\Tools;

use App\Models\SiteStatistic;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves official school statistics as displayed on the public website hero section.
 */
class GetSiteStatistics implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil statistik resmi sekolah yang ditampilkan di website publik, seperti jumlah total siswa, guru, alumni, prestasi, dan data statistik lainnya yang sudah diverifikasi admin.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $statistics = SiteStatistic::query()
            ->select(['label', 'value', 'section'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($statistics->isEmpty()) {
            return json_encode([
                'found' => false,
                'message' => 'Statistik resmi sekolah belum diisi oleh admin.',
            ]);
        }

        return json_encode([
            'found' => true,
            'statistics' => $statistics->map(fn ($s) => [
                'label' => $s->label,
                'value' => $s->value,
            ])->values()->all(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
