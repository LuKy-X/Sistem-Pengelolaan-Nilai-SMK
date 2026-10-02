<?php

namespace App\Http\Requests\Student;

use App\Models\ExitPermit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExitPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExitPermit::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason_id' => [
                'required',
                Rule::exists('exit_permit_reasons', 'id')->where('is_active', true),
            ],
            'reason_detail' => ['required', 'string', 'min:10', 'max:1000'],
            'planned_exit_at' => ['required', 'date', 'after_or_equal:today'],
            'planned_return_at' => ['required', 'date', 'after:planned_exit_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason_id.required' => 'Pilih alasan izin keluar.',
            'reason_id.exists' => 'Alasan izin tidak valid atau sudah nonaktif.',
            'reason_detail.required' => 'Jelaskan detail keperluan Anda.',
            'reason_detail.min' => 'Detail keperluan minimal 10 karakter.',
            'planned_exit_at.required' => 'Tentukan rencana waktu keluar.',
            'planned_exit_at.after_or_equal' => 'Waktu keluar tidak boleh di masa lalu.',
            'planned_return_at.required' => 'Tentukan rencana waktu kembali.',
            'planned_return_at.after' => 'Waktu kembali harus setelah waktu keluar.',
        ];
    }
}
