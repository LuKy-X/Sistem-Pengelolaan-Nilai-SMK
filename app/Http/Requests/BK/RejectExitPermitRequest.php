<?php

namespace App\Http\Requests\BK;

use App\Models\ExitPermit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RejectExitPermitRequest extends FormRequest
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
            'rejection_note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rejection_note.required' => 'Alasan penolakan wajib diisi agar siswa mengetahui penyebabnya.',
            'rejection_note.min' => 'Alasan penolakan minimal 5 karakter.',
            'rejection_note.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
