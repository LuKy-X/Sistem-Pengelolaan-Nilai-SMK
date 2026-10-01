<?php

namespace App\Http\Requests\BK;

use App\Models\ExitPermit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'approval_note.max' => 'Catatan persetujuan maksimal 1000 karakter.',
        ];
    }
}
