<?php

namespace App\Ai\Tools;

use App\Models\TeacherProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Counts active teachers in the school.
 */
class GetTeacherCount implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Menghitung jumlah guru aktif di sekolah. Bisa difilter berdasarkan jenis kelamin atau status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $gender = $request['gender'] ?? null;

        $query = TeacherProfile::query()->where('status', 'ACTIVE');

        if ($gender) {
            $normalizedGender = strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false ? 'MALE' : 'FEMALE';
            $query->where('gender', $normalizedGender);
        }

        $count = $query->count();

        return json_encode([
            'count' => $count,
            'filter' => $gender ? (strtoupper($gender) === 'L' || stripos($gender, 'laki') !== false ? 'laki-laki' : 'perempuan') : null,
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
        ];
    }
}
