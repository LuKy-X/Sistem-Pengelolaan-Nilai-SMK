<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'exists:teacher_profiles,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'weekly_hours' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
