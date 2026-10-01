<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The `login` field accepts either an email address or a username, so the
     * email format rule must only apply when the input actually looks like an
     * email. See `LoginController::login()` for how the value is resolved.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::when(
                    $this->looksLikeEmail(),
                    'email:filter',
                    'regex:/^[A-Za-z0-9._-]+$/',
                ),
            ],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Email atau Username wajib diisi.',
            'login.email' => 'Format email tidak valid.',
            'login.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'login.max' => 'Email atau Username maksimal 255 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Treat the input as an email only when it contains an "@", otherwise it
     * is a username and is validated with the username character set.
     */
    private function looksLikeEmail(): bool
    {
        return str_contains((string) $this->input('login'), '@');
    }
}
