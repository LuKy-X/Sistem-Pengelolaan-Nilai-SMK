<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\ClassEnrollment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentsController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');

        $students = StudentProfile::with([
            'user',
            'classEnrollments' => function ($q) {
                $q->where('status', 'ACTIVE')->with('schoolClass');
            },
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($classId, function ($query, $classId) {
                $query->whereHas('classEnrollments', function ($q) use ($classId) {
                    $q->where('class_id', $classId)->where('status', 'ACTIVE');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.academic.students.index', compact('students', 'classes', 'search', 'classId'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = null;
            if (! empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'username' => $validated['nis'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password'] ?? 'password123'),
                    'is_active' => true,
                ]);

                $studentRole = Role::where('code', 'student')->first();
                if ($studentRole) {
                    $user->roles()->attach($studentRole->id);
                }
            }

            $student = StudentProfile::create([
                'user_id' => $user?->id,
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'],
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => 'ACTIVE',
            ]);

            if (! empty($validated['class_id'])) {
                ClassEnrollment::create([
                    'class_id' => $validated['class_id'],
                    'student_id' => $student->id,
                    'start_date' => now(),
                    'status' => 'ACTIVE',
                ]);
            }
        });

        return redirect()->route('admin.academic.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(StudentProfile $student): View
    {
        $student->load([
            'user',
            'classEnrollments.schoolClass.department',
            'disciplineRecords',
            'exitPermits',
        ]);

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.academic.students.show', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, StudentProfile $student): RedirectResponse
    {
        $validated = $request->validated();
        $student->update($validated);

        if ($student->user) {
            $student->user->update(['name' => $validated['full_name']]);
        }

        return redirect()->route('admin.academic.students.show', $student)
            ->with('success', 'Profil siswa berhasil diperbarui.');
    }

    public function destroy(StudentProfile $student): RedirectResponse
    {
        if ($student->gradebookScores()->exists() || $student->journalAttendances()->exists()) {
            return redirect()->route('admin.academic.students.index')
                ->with('error', 'Tidak dapat menghapus data siswa yang telah memiliki histori penilaian atau presensi.');
        }

        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->classEnrollments()->delete();
            $student->delete();
            if ($user) {
                $user->roles()->detach();
                $user->delete();
            }
        });

        return redirect()->route('admin.academic.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    public function enrollClass(Request $request, StudentProfile $student): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
        ]);

        // Nonaktifkan enrollment lama
        ClassEnrollment::where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->update([
                'status' => 'TRANSFERRED',
                'end_date' => now(),
            ]);

        ClassEnrollment::updateOrCreate(
            ['class_id' => $validated['class_id'], 'student_id' => $student->id],
            ['status' => 'ACTIVE', 'start_date' => now(), 'end_date' => null]
        );

        return redirect()->back()->with('success', 'Penempatan rombel siswa berhasil diperbarui.');
    }
}
