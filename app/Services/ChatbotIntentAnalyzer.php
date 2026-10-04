<?php

namespace App\Services;

class ChatbotIntentAnalyzer
{
    /**
     * Classify the part of a question that should control answer selection.
     * Department names are deliberately left to the database retriever.
     *
     * @return array{
     *     intent: string|null,
     *     role: string|null,
     *     context: string|null,
     *     requires_ai: bool,
     *     confidence: float,
     *     reason: string
     * }
     */
    public function analyze(string $question): array
    {
        $normalized = $this->normalize($question);

        if ($this->containsAny($normalized, ['prospek kerja', 'peluang kerja', 'prospek karier', 'prospek karir', 'lulusan rpl', 'lulusan jurusan'])) {
            return $this->result('department_career', false, 0.95, 'career_prospect_question');
        }

        if ($this->containsAny($normalized, [
            'mata pelajaran', 'mapel', 'pelajaran apa', 'belajar apa', 'kurikulum',
            'kompetensi yang dilatih', 'kompetensi apa', 'kompetensi di', 'kompetensi jurusan',
        ])) {
            return $this->result('department_curriculum', false, 0.94, 'department_curriculum_question');
        }

        if ($this->containsAny($normalized, ['fasilitas', 'alat praktik', 'peralatan praktik', 'laboratorium'])) {
            return $this->result('department_facility', false, 0.92, 'facility_question');
        }

        if ($this->containsAny($normalized, ['kepala sekolah', 'kepsek'])) {
            return $this->result('principal', false, 0.96, 'principal_question');
        }

        $mentionsParent = $this->containsAny($normalized, ['orang tua', 'ortu', 'wali murid', 'bapak ibu', 'ayah', 'ibu']);
        $mentionsChild = $this->containsAny($normalized, ['anak saya', 'anak mulai sekolah', 'anak diterima', 'anak keterima']);
        $mentionsStudent = $this->containsAny($normalized, ['saya masuk', 'saya diterima', 'saya keterima', 'aku masuk', 'aku diterima']);
        $asksForPreparation = $this->containsAny($normalized, [
            'siapkan', 'persiap', 'dibawa', 'bawa', 'perlu', 'harus', 'wajib', 'beli', 'membeli',
            'perlengkapan', 'kebutuhan', 'aturan', 'boleh', 'dilarang',
        ]);
        $asksBeforeSchool = str_contains($normalized, 'sebelum anak mulai sekolah')
            || str_contains($normalized, 'sebelum mulai sekolah');

        if ($asksForPreparation || $asksBeforeSchool) {
            $role = $mentionsParent || $mentionsChild
                ? 'orang_tua'
                : ($mentionsStudent ? 'siswa' : null);
            $context = $this->containsAny($normalized, ['diterima', 'keterima', 'masuk sekolah', 'mulai sekolah'])
                ? 'persiapan_masuk_sekolah'
                : null;

            return [
                ...$this->result(
                    $role === 'orang_tua' ? 'parent_preparation' : 'school_preparation',
                    true,
                    0.94,
                    'preparation_or_policy_question',
                ),
                'role' => $role,
                'context' => $context,
            ];
        }

        if ($this->containsAny($normalized, ['jurusan itu', 'yang tadi', 'yang ini', 'kalau begitu', 'terus bagaimana', 'terus biayanya', 'kalau anak saya', 'bagaimana kalau'])) {
            return $this->result('follow_up', true, 0.88, 'follow_up_requires_conversation_context');
        }

        return $this->result(null, false, 0.0, 'no_semantic_intent_match');
    }

    /** @param list<string> $phrases */
    private function containsAny(string $value, array $phrases): bool
    {
        foreach ($phrases as $phrase) {
            if (str_contains($value, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /** @return array{intent: string|null, role: string|null, context: string|null, requires_ai: bool, confidence: float, reason: string} */
    private function result(?string $intent, bool $requiresAi, float $confidence, string $reason): array
    {
        return [
            'intent' => $intent,
            'role' => null,
            'context' => null,
            'requires_ai' => $requiresAi,
            'confidence' => $confidence,
            'reason' => $reason,
        ];
    }
}
