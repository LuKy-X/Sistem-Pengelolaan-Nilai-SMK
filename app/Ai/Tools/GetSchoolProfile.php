<?php

namespace App\Ai\Tools;

use App\Models\SchoolProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Retrieves basic school profile information including name, address, vision, and mission.
 */
class GetSchoolProfile implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil profil sekolah termasuk nama, alamat, telepon, email, website, visi, misi, sejarah, dan nama kepala sekolah.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $profile = SchoolProfile::query()->first();

        if ($profile === null) {
            return json_encode(['error' => 'Profil sekolah belum diisi oleh admin.']);
        }

        return json_encode([
            'school_name' => $profile->school_name,
            'address' => $profile->address,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'website' => $profile->website,
            'principal_name' => $profile->principal_name,
            'vision' => $profile->vision,
            'mission' => $profile->mission,
            'description' => $profile->description,
            'history' => $profile->history,
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
