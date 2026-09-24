<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile;

        return view('teacher.profile.index', compact('user', 'teacher'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:L,P,MALE,FEMALE'],
        ]);

        $genderValue = in_array(strtoupper($validated['gender']), ['L', 'MALE']) ? 'MALE' : 'FEMALE';

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($teacher) {
            $teacher->update([
                'full_name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $genderValue,
            ]);
        }

        return redirect()->route('teacher.profile.index')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('teacher.profile.index')->with('success', 'Kata sandi berhasil diubah.');
    }
}
