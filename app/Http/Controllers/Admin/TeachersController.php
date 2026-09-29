<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeachersController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $teachers = TeacherProfile::with(['user', 'homeroomClasses'])
            ->withCount('teachingAssignments')
            ->when($search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.teachers.index', compact('teachers', 'search'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'username' => $validated['username'] ?? $validated['nip'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'guru12345'),
                'is_active' => true,
            ]);

            $teacherRole = Role::where('code', 'teacher')->first();
            if ($teacherRole) {
                $user->roles()->attach($teacherRole->id);
            }

            TeacherProfile::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => 'ACTIVE',
            ]);
        });

        return redirect()->route('admin.users.teachers.index')
            ->with('success', 'Data guru dan akun login berhasil ditambahkan.');
    }

    public function show(TeacherProfile $teacher): View
    {
        $teacher->load([
            'user',
            'homeroomClasses.academicYear',
            'teachingAssignments.subject',
            'teachingAssignments.schoolClass',
            'teachingAssignments.semester',
        ]);

        return view('admin.users.teachers.show', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, TeacherProfile $teacher): RedirectResponse
    {
        $validated = $request->validated();
        $teacher->update($validated);

        if ($teacher->user) {
            $teacher->user->update(['name' => $validated['full_name']]);
        }

        return redirect()->route('admin.users.teachers.index')
            ->with('success', 'Data profil guru berhasil diperbarui.');
    }

    public function destroy(TeacherProfile $teacher): RedirectResponse
    {
        if ($teacher->teachingAssignments()->exists() || $teacher->homeroomClasses()->exists()) {
            return redirect()->route('admin.users.teachers.index')
                ->with('error', 'Tidak dapat menghapus guru yang masih aktif mengajar atau menjadi wali kelas.');
        }

        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();
            if ($user) {
                $user->roles()->detach();
                $user->delete();
            }
        });

        return redirect()->route('admin.users.teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
