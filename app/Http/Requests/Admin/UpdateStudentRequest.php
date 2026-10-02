<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? $this->route('student');

        return [
            'nis' => ['required', 'string', 'max:20', Rule::unique('student_profiles', 'nis')->ignore($studentId)],
            'nisn' => ['required', 'string', 'max:20', Rule::unique('student_profiles', 'nisn')->ignore($studentId)],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:MALE,FEMALE'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
