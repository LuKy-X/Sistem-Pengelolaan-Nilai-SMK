<?php

namespace App\Ai\Tools;

use App\Models\Subject;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves list of active subjects, optionally filtered by department.
 */
class GetSubjectList implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil daftar mata pelajaran yang tersedia. Bisa difilter berdasarkan jurusan atau kategori.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $departmentSearch = $request['department'] ?? null;
        $category = $request['category'] ?? null;

        $query = Subject::query()
            ->select(['subjects.id', 'subjects.code', 'subjects.name', 'subjects.category'])
            ->with(['department:id,name,short_name'])
            ->where('subjects.is_active', true)
            ->orderBy('subjects.name');

        if ($departmentSearch) {
            $query->whereHas('department', function ($q) use ($departmentSearch) {
                $q->where('name', 'like', "%{$departmentSearch}%")
                    ->orWhere('short_name', 'like', "%{$departmentSearch}%")
                    ->orWhere('code', 'like', "%{$departmentSearch}%");
            });
        }

        if ($category) {
            $query->where('subjects.category', 'like', "%{$category}%");
        }

        $subjects = $query->get();

        return json_encode([
            'subjects' => $subjects->map(fn ($s) => [
                'code' => $s->code,
                'name' => $s->name,
                'category' => $s->category,
                'department' => $s->department ? ($s->department->short_name ?: $s->department->name) : 'Umum',
            ])->values()->all(),
            'total' => $subjects->count(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'department' => $schema->string()
                ->description('Nama atau kode jurusan untuk memfilter mata pelajaran jurusan tersebut.'),
            'category' => $schema->string()
                ->description('Kategori mata pelajaran jika diketahui.'),
        ];
    }
}
