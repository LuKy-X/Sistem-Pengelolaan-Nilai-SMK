<?php

namespace App\Ai\Tools;

use App\Models\SchoolClass;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves list of active school classes.
 */
class GetClassList implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil daftar kelas/rombel yang aktif. Bisa difilter berdasarkan jurusan atau tingkat kelas (X/XI/XII).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $departmentSearch = $request['department'] ?? null;
        $gradeLevelCode = $request['grade_level'] ?? null;

        $query = SchoolClass::query()
            ->with(['department:id,name,short_name', 'gradeLevel:id,code,name', 'homeroomTeacher:id,full_name'])
            ->where('is_active', true)
            ->orderBy('name');

        if ($departmentSearch) {
            $query->whereHas('department', function ($q) use ($departmentSearch) {
                $q->where('name', 'like', "%{$departmentSearch}%")
                    ->orWhere('short_name', 'like', "%{$departmentSearch}%")
                    ->orWhere('code', 'like', "%{$departmentSearch}%");
            });
        }

        if ($gradeLevelCode) {
            $query->whereHas('gradeLevel', function ($q) use ($gradeLevelCode) {
                $q->where('code', 'like', "%{$gradeLevelCode}%")
                    ->orWhere('name', 'like', "%{$gradeLevelCode}%");
            });
        }

        $classes = $query->get();

        return json_encode([
            'classes' => $classes->map(fn ($c) => [
                'code' => $c->code,
                'name' => $c->name,
                'grade_level' => $c->gradeLevel?->code,
                'department' => $c->department ? ($c->department->short_name ?: $c->department->name) : null,
                'homeroom_teacher' => $c->homeroomTeacher?->full_name,
            ])->values()->all(),
            'total' => $classes->count(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'department' => $schema->string()
                ->description('Nama atau kode jurusan. Contoh: "RPL", "TKJT".'),
            'grade_level' => $schema->string()
                ->description('Tingkat kelas: "X", "XI", atau "XII".'),
        ];
    }
}
