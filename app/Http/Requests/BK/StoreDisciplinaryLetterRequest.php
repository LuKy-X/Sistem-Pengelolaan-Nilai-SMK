<?php

namespace App\Http\Requests\BK;

use App\Enums\DisciplinaryLetterType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisciplinaryLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isCounselor();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'type' => ['required', Rule::enum(DisciplinaryLetterType::class)],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'issued_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'siswa',
            'type' => 'jenis surat peringatan',
            'reason' => 'alasan penerbitan',
            'issued_at' => 'tanggal penerbitan',
            'document' => 'dokumen pendukung',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Pilih siswa yang menerima surat peringatan.',
            'type.required' => 'Pilih jenis surat peringatan (SP1/SP2/SP3).',
            'type.enum' => 'Jenis surat peringatan tidak valid.',
            'reason.required' => 'Alasan penerbitan surat peringatan wajib diisi.',
            'reason.min' => 'Alasan penerbitan minimal 10 karakter.',
            'issued_at.before_or_equal' => 'Tanggal penerbitan tidak boleh melewati hari ini.',
            'document.mimes' => 'Dokumen pendukung harus berupa PDF, JPG, atau PNG.',
            'document.max' => 'Ukuran dokumen pendukung maksimal 2 MB.',
        ];
    }
}
