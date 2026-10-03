<?php

namespace App\Http\Requests\BK;

use App\Models\ExitPermit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class ApproveExitPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permit = $this->route('permit');

        return $permit instanceof ExitPermit && Gate::allows('process', $permit);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'approval_note' => ['nullable', 'string', 'max:1000'],
            'approved_exit_at' => ['nullable', 'date'],
            'approved_return_at' => ['nullable', 'date', 'after:approved_exit_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'approval_note.max' => 'Catatan persetujuan maksimal 1000 karakter.',
            'approved_exit_at.date' => 'Jam keluar yang disetujui tidak valid.',
            'approved_return_at.date' => 'Jam kembali yang disetujui tidak valid.',
            'approved_return_at.after' => 'Jam kembali yang disetujui harus setelah jam keluar yang disetujui.',
        ];
    }

    /**
     * Jam kembali wajib diisi bila BK menetapkan jam keluar sendiri, dan tidak boleh
     * mendahului rencana keluar yang diajukan siswa.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $permit = $this->route('permit');

            if (! $permit instanceof ExitPermit) {
                return;
            }

            $exitAt = $this->input('approved_exit_at');
            $returnAt = $this->input('approved_return_at');

            if ($exitAt !== null && $returnAt === null) {
                $validator->errors()->add('approved_return_at', 'Tentukan jam kembali bila jam keluar diubah.');
            }

            if ($returnAt !== null && strtotime((string) $returnAt) < $permit->planned_exit_at->getTimestamp()) {
                $validator->errors()->add('approved_return_at', 'Jam kembali yang disetujui tidak boleh lebih awal dari rencana keluar siswa.');
            }
        });
    }
}
