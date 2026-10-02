<?php

namespace App\Ai\Tools;

use App\Models\SchoolClass;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Finds the homeroom teacher (wali kelas) of a specific class.
 */
class GetWaliKelas implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mencari wali kelas (homeroom teacher) dari suatu kelas tertentu. Masukkan nama atau kode kelas untuk mendapatkan informasi wali kelas.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $classSearch = $request['class'] ?? null;

        if (! $classSearch) {
            return json_encode(['error' => 'Nama atau kode kelas diperlukan.']);
        }

        $classes = SchoolClass::query()
            ->with([
                'homeroomTeacher:id,full_name,nip,gender',
                'department:id,name,short_name',
                'gradeLevel:id,code,name',
            ])
            ->where('is_active', true)
            ->where(function ($q) use ($classSearch) {
                $q->where('name', 'like', "%{$classSearch}%")
                    ->orWhere('code', 'like', "%{$classSearch}%");
            })
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            return json_encode([
                'found' => false,
                'message' => "Kelas '{$classSearch}' tidak ditemukan atau tidak aktif.",
            ]);
        }

        return json_encode([
            'found' => true,
            'classes' => $classes->map(fn ($c) => [
                'class_name' => $c->name,
                'class_code' => $c->code,
                'grade_level' => $c->gradeLevel?->code,
                'department' => $c->department ? ($c->department->short_name ?: $c->department->name) : null,
                'homeroom_teacher' => $c->homeroomTeacher ? [
                    'name' => $c->homeroomTeacher->full_name,
                    'nip' => $c->homeroomTeacher->nip,
                    'gender' => $c->homeroomTeacher->gender === 'MALE' ? 'Laki-laki' : 'Perempuan',
                ] : null,
            ])->values()->all(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'class' => $schema->string()
                ->description('Nama atau kode kelas. Contoh: "XII RPL C", "RPL A", "XI TKJT B".')
                ->required(),
        ];
    }
}
