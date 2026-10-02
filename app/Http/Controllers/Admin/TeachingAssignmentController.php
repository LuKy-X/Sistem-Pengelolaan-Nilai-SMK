<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeachingAssignmentRequest;
use App\Http\Requests\Admin\UpdateTeachingAssignmentRequest;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        // Pastikan akun Guru BK memiliki TeacherProfile aktif
        TeacherProfile::ensureCounselorProfiles();

        $activeSemester = Semester::where('is_active', true)->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('class_id');
        $search = $request->query('search');

        $assignments = TeachingAssignment::with([
            'teacher.user.roles',
            'subject',
            'schoolClass.department',
            'schoolClass.gradeLevel',
            'semester.academicYear',
        ])
            ->when($selectedSemesterId, function ($query, $semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->when($selectedClassId, function ($query, $classId) {
                $query->where('class_id', $classId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->whereHas('teacher', function ($t) use ($search) {
                        $t->where('full_name', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    })
                        ->orWhereHas('subject', function ($s) use ($search) {
                            $s->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolClass', function ($c) use ($search) {
                            $c->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $semesters = Semester::with('academicYear')->latest('start_date')->get();

        // Ambil seluruh guru dan kelompokkan menjadi Guru Reguler dan Guru BK
        $allTeachers = TeacherProfile::with('user.roles')
            ->where('status', 'ACTIVE')
            ->orderBy('full_name')
            ->get();

        $regularTeachers = $allTeachers->filter(function ($t) {
            return ! $t->isCounselor();
        })->values();

        $counselorTeachers = $allTeachers->filter(function ($t) {
            return $t->isCounselor();
        })->values();

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();

        return view('admin.academic.teaching-assignments.index', [
            'assignments' => $assignments,
            'semesters' => $semesters,
            'teachers' => $allTeachers,
            'regularTeachers' => $regularTeachers,
            'counselorTeachers' => $counselorTeachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedClassId' => $selectedClassId,
            'search' => $search,
        ]);
    }

    public function store(StoreTeachingAssignmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['weekly_hours'] = $validated['weekly_hours'] ?? 2;

        // Cek duplikasi
        $exists = TeachingAssignment::where([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'semester_id' => $validated['semester_id'],
        ])->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Penugasan mengajar untuk kombinasi guru, mapel, kelas, dan semester tersebut sudah ada.');
        }

        TeachingAssignment::create($validated);

        return redirect()->route('admin.academic.teaching-assignments.index', ['semester_id' => $validated['semester_id']])
            ->with('success', 'Penugasan guru mengajar berhasil disimpan.');
    }

    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['weekly_hours'] = $validated['weekly_hours'] ?? 2;

        // Cek duplikasi penugasan selain ID saat ini
        $exists = TeachingAssignment::where('id', '!=', $teachingAssignment->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('class_id', $validated['class_id'])
            ->where('semester_id', $validated['semester_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Penugasan mengajar untuk kombinasi guru, mapel, kelas, dan semester tersebut sudah ada.');
        }

        $teachingAssignment->update([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'semester_id' => $validated['semester_id'],
            'weekly_hours' => $validated['weekly_hours'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Data penugasan mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingAssignment $teachingAssignment): RedirectResponse
    {
        if ($teachingAssignment->gradebooks()->exists() || $teachingAssignment->journals()->exists()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus penugasan mengajar yang sudah memiliki buku nilai atau catatan jurnal absensi.');
        }

        $teachingAssignment->delete();

        return redirect()->back()->with('success', 'Penugasan guru mengajar berhasil dihapus.');
    }
}
