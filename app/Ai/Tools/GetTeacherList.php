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
        return 'Mengambil daftar guru aktif di sekolah. Bisa difilter berdasarkan jenis kelamin. Hasilnya dipaginasi (max 20 per halaman).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $gender = $request['gender'] ?? null;
        $page = max(1, (int) ($request['page'] ?? 1));

        $query = TeacherProfile::query()
            ->select(['id', 'full_name', 'nip', 'gender', 'status'])
            ->where('status', 'ACTIVE')
            ->orderBy('full_name');

        if ($gender) {
            $normalizedGender = strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false ? 'MALE' : 'FEMALE';
            $query->where('gender', $normalizedGender);
        }

        $total = $query->count();
        $teachers = $query->forPage($page, self::MAX_PER_PAGE)->get();

        return json_encode([
            'teachers' => $teachers->map(fn ($t) => [
                'full_name' => $t->full_name,
                'nip' => $t->nip,
                'gender' => $t->gender === 'MALE' ? 'Laki-laki' : 'Perempuan',
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
            'gender' => $schema->string()
                ->description('Jenis kelamin: "L" untuk laki-laki, "P" untuk perempuan.'),
            'page' => $schema->integer()
                ->description('Halaman data (default: 1).')
                ->default(1),
        ];
    }
}
