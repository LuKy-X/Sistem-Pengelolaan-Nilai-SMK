<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id ?? $this->route('teacher');

        return [
            'nip' => ['required', 'string', 'max:30', Rule::unique('teacher_profiles', 'nip')->ignore($teacherId)],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:MALE,FEMALE'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
