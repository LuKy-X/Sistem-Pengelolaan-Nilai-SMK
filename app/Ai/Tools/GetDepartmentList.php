<?php

namespace App\Ai\Tools;

use App\Models\Department;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves list of active school departments/majors.
 */
class GetDepartmentList implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil daftar jurusan/program keahlian yang tersedia di sekolah, beserta kode, nama singkat, deskripsi, dan informasi jurusan.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $onlyActive = filter_var($request['only_active'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

        $query = Department::query()
            ->select(['id', 'code', 'name', 'short_name', 'description', 'career_prospects', 'is_active'])
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $departments = $query->get();

        if ($departments->isEmpty()) {
            return json_encode(['departments' => [], 'total' => 0]);
        }

        return json_encode([
            'departments' => $departments->map(fn ($dept) => [
                'id' => $dept->id,
                'code' => $dept->code,
                'name' => $dept->name,
                'short_name' => $dept->short_name,
                'description' => $dept->description ? mb_substr($dept->description, 0, 200) : null,
                'career_prospects' => $dept->career_prospects ? mb_substr($dept->career_prospects, 0, 150) : null,
                'is_active' => $dept->is_active,
            ])->values()->all(),
            'total' => $departments->count(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'only_active' => $schema->boolean()
                ->description('Apakah hanya menampilkan jurusan aktif. Default: true.')
                ->default(true),
        ];
    }
}
