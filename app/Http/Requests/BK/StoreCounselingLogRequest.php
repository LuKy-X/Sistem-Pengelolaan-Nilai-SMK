<?php

namespace App\Http\Requests\BK;

use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCounselingLogRequest extends FormRequest
{
    /**
     * Jenis layanan bimbingan & konseling yang direkam sebagai jejak konseling.
     *
     * @var list<string>
     */
    public const SERVICE_TYPES = [
        'COUNSELING',
        'INTERVIEW',
        'HOME_VISIT',
        'PARENT_MEETING',
    ];

    public function authorize(): bool
    {
        return Gate::allows('create', DisciplineRecord::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'service_type' => ['required', Rule::in(self::SERVICE_TYPES)],
            'category_id' => ['required', 'integer', Rule::exists(DisciplineCategory::class, 'id')->where('is_active', true)],
            'occurred_at' => ['required', 'date', 'before_or_equal:today'],
            'summary' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Pilih siswa yang menerima layanan konseling.',
            'service_type.required' => 'Pilih jenis layanan konseling.',
            'service_type.in' => 'Jenis layanan konseling tidak valid.',
            'category_id.required' => 'Pilih kategori catatan konseling.',
            'category_id.exists' => 'Kategori catatan tidak ditemukan atau tidak aktif.',
            'occurred_at.before_or_equal' => 'Tanggal layanan tidak boleh melewati hari ini.',
            'summary.required' => 'Ringkasan hasil konseling wajib diisi.',
            'summary.min' => 'Ringkasan hasil konseling minimal 10 karakter.',
        ];
    }
}
