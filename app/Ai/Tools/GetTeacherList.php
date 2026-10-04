<?php

namespace App\Ai\Tools;

use App\Models\TeacherProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves a paginated list of active teachers.
 */
class GetTeacherList implements Tool
{
    private const MAX_PER_PAGE = 20;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil nama guru aktif di sekolah. Hasilnya dipaginasi (max 20 per halaman) tanpa nomor identitas atau atribut pribadi.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $page = max(1, (int) ($request['page'] ?? 1));

        $query = TeacherProfile::query()
            ->select(['id', 'full_name', 'status'])
            ->where('status', 'ACTIVE')
            ->orderBy('full_name');

        $total = $query->count();
        $teachers = $query->forPage($page, self::MAX_PER_PAGE)->get();

        return json_encode([
            'teachers' => $teachers->map(fn ($t) => [
                'full_name' => $t->full_name,
            ])->values()->all(),
            'total' => $total,
            'page' => $page,
            'per_page' => self::MAX_PER_PAGE,
            'has_more' => $total > $page * self::MAX_PER_PAGE,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->integer()
                ->description('Halaman data (default: 1).')
                ->default(1),
        ];
    }
}
