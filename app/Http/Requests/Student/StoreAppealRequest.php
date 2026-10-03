<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permit = $this->route('permit');

        return $permit !== null
            && $this->user()?->can('appeal', $permit);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Jelaskan alasan banding keterlambatan Anda.',
            'reason.min' => 'Alasan banding minimal 10 karakter.',
        ];
    }
}
