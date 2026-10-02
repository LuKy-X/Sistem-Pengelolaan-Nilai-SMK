<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nip' => ['required', 'string', 'max:30', 'unique:teacher_profiles,nip'],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:MALE,FEMALE'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
