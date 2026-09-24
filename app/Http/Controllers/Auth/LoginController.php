<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $loginInput = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Determine if input is email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'login' => 'Kombinasi email/username dan kata sandi tidak cocok.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin sekolah.',
            ]);
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isTeacher()) {
            return redirect()->intended(route('teacher.dashboard'));
        }

        if ($user->isCounselor()) {
            return redirect()->intended(route('counselor.dashboard'));
        }

        if ($user->isStudent()) {
            return redirect()->intended(route('student.dashboard'));
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
