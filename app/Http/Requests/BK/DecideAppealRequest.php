<?php

namespace App\Http\Requests\BK;

use App\Enums\AppealDecision;
use App\Enums\DisciplineCategoryType;
use App\Models\DisciplineCategory;
use App\Models\ExitPermitAppeal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appeal = $this->route('appeal');

        return $appeal instanceof ExitPermitAppeal
            && (bool) $this->user()?->isCounselor();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(AppealDecision::class)->only([AppealDecision::Accepted, AppealDecision::Rejected])],
            'decision_note' => ['nullable', 'required_if:decision,REJECTED', 'string', 'max:1000'],
            'record_sanction' => ['nullable', 'boolean'],
            'sanction_category_id' => [
                'nullable',
                'required_if:record_sanction,1,on',
                Rule::exists(DisciplineCategory::class, 'id')->where('is_active', true),
            ],
            'sanction_points' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan banding wajib dipilih.',
            'decision.enum' => 'Keputusan banding tidak valid.',
            'decision_note.required_if' => 'Catatan keputusan wajib diisi saat banding ditolak.',
            'sanction_category_id.required_if' => 'Pilih jenis pelanggaran yang akan dicatat sebagai sanksi.',
            'sanction_category_id.exists' => 'Kategori pelanggaran tidak ditemukan atau tidak aktif.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sanction_points') && $this->input('sanction_points') !== null && $this->input('sanction_points') !== '') {
            $this->merge(['sanction_points' => abs((int) $this->input('sanction_points'))]);
        }
    }

    /**
     * Delta poin yang dicatat sebagai sanksi akibat banding yang ditolak.
     */
    public function resolvedSanctionPoints(?DisciplineCategory $category): int
    {
        $requested = $this->filled('sanction_points') ? (int) $this->input('sanction_points') : null;

        if ($requested === null) {
            return (int) ($category?->default_points ?? 0);
        }

        return $category?->type === DisciplineCategoryType::Reward ? $requested : -$requested;
    }
}
