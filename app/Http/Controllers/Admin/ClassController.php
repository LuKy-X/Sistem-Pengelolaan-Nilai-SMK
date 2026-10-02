<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\AlumniProfile;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with([
            'academicYear',
            'department',
            'gradeLevel',
            'homeroomTeacher',
        ])
            ->withCount([
                'enrollments' => fn ($q) => $q->where('status', 'ACTIVE'),
                'teachingAssignments',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $academicYears = AcademicYear::latest('start_date')->get();
        $departments = Department::where('is_active', true)->get();
        $gradeLevels = GradeLevel::all();
        $teachers = TeacherProfile::where('status', 'ACTIVE')->orderBy('full_name')->get();

        return view('admin.academic.classes.index', compact(
            'classes',
            'academicYears',
            'departments',
            'gradeLevels',
            'teachers'
        ));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        SchoolClass::create($validated);

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Rombongan belajar (kelas) berhasil dibuat.');
    }

    public function show(SchoolClass $class): View
    {
        $class->load([
            'academicYear',
            'department',
            'gradeLevel',
            'homeroomTeacher',
            'enrollments' => fn ($q) => $q->where('status', 'ACTIVE')->with('student'),
            'teachingAssignments.subject',
            'teachingAssignments.teacher',
        ]);

        return view('admin.academic.classes.show', compact('class'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $class->update($validated);

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Data rombongan belajar berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->enrollments()->exists() || $class->teachingAssignments()->exists()) {
            return redirect()->route('admin.academic.classes.index')
                ->with('error', 'Tidak dapat menghapus kelas yang sudah memiliki siswa terdaftar atau jadwal mengajar.');
        }

        $class->delete();

        return redirect()->route('admin.academic.classes.index')
            ->with('success', 'Rombongan belajar berhasil dihapus.');
    }

    /**
     * Mengambil data siswa aktif di rombel untuk pratinjau dan seleksi kenaikan kelas.
     */
    public function studentsForPromotion(SchoolClass $class): JsonResponse
    {
        $class->load([
            'gradeLevel',
            'department',
            'academicYear',
            'enrollments' => fn ($q) => $q->where('status', 'ACTIVE')->with('student.user'),
        ]);

        $currentLevelCode = $class->gradeLevel?->code; // X, XI, XII

        $nextLevelCode = match ($currentLevelCode) {
            'X' => 'XI',
            'XI' => 'XII',
            default => null, // XII adalah tingkat akhir (Kelulusan / Alumni)
        };

        $nextLevel = $nextLevelCode ? GradeLevel::where('code', $nextLevelCode)->first() : null;

        // Sugesti Cerdas Nama dan Kode Kelas Tingkat Berikutnya
        $suggestedName = $class->name;
        $suggestedCode = $class->code;

        if ($currentLevelCode === 'XI' && $nextLevelCode === 'XII') {
            $suggestedName = preg_replace('/\bXI\b/', 'XII', $class->name);
            $suggestedCode = preg_replace('/\bXI\b/', 'XII', $class->code);
            if ($suggestedName === $class->name) {
                $suggestedName = 'XII '.$class->name;
            }
        } elseif ($currentLevelCode === 'X' && $nextLevelCode === 'XI') {
            $suggestedName = preg_replace('/\bX\b/', 'XI', $class->name);
            $suggestedCode = preg_replace('/\bX\b/', 'XI', $class->code);
            if ($suggestedName === $class->name) {
                $suggestedName = 'XI '.$class->name;
            }
        }

        // Daftar rombel target yang sudah ada pada tingkat berikutnya
        $availableTargetClasses = [];
        if ($nextLevel) {
            $availableTargetClasses = SchoolClass::with('academicYear')
                ->withCount(['enrollments' => fn ($q) => $q->where('status', 'ACTIVE')])
                ->where('grade_level_id', $nextLevel->id)
                ->where('id', '!=', $class->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn ($tc) => [
                    'id' => $tc->id,
                    'name' => $tc->name,
                    'code' => $tc->code,
                    'academic_year' => $tc->academicYear?->name,
                    'department_id' => $tc->department_id,
                    'active_students_count' => $tc->enrollments_count,
                ]);
        }

        $students = $class->enrollments->map(function ($enrollment) {
            $student = $enrollment->student;

            return [
                'id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'full_name' => $student->full_name,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'gender' => $student->gender,
                'status' => $enrollment->status,
            ];
        })->sortBy('full_name')->values();

        return response()->json([
            'source_class' => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade_level_id' => $class->grade_level_id,
                'grade_level_code' => $currentLevelCode,
                'grade_level_name' => $class->gradeLevel?->name ?? 'Tingkat',
                'department_id' => $class->department_id,
                'department_name' => $class->department?->name ?? '-',
                'academic_year_id' => $class->academic_year_id,
                'academic_year_name' => $class->academicYear?->name ?? '-',
            ],
            'is_graduation' => ($currentLevelCode === 'XII' || $nextLevelCode === null),
            'next_level' => $nextLevel ? [
                'id' => $nextLevel->id,
                'code' => $nextLevel->code,
                'name' => $nextLevel->name,
            ] : null,
            'suggested_name' => $suggestedName,
            'suggested_code' => $suggestedCode,
            'available_target_classes' => $availableTargetClasses,
            'students' => $students,
            'total_students' => $students->count(),
        ]);
    }

    /**
     * Memproses Kenaikan Kelas / Kelulusan Rombel (100% Menggunakan Database yang Ada).
     */
    public function promote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_class_id' => ['required', 'exists:classes,id'],
            'action_type' => ['required', 'in:existing,create_new,graduate'],
            'target_class_id' => ['required_if:action_type,existing', 'nullable', 'exists:classes,id'],
            'new_class_name' => ['required_if:action_type,create_new', 'nullable', 'string', 'max:100'],
            'new_class_code' => ['required_if:action_type,create_new', 'nullable', 'string', 'max:30'],
            'target_academic_year_id' => ['required_if:action_type,create_new', 'nullable', 'exists:academic_years,id'],
            'new_homeroom_teacher_id' => ['nullable', 'exists:teacher_profiles,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:student_profiles,id'],
            'promotion_date' => ['nullable', 'date'],
            'deactivate_source_class' => ['nullable', 'boolean'],
        ]);

        $sourceClass = SchoolClass::with(['gradeLevel', 'department'])->findOrFail($validated['source_class_id']);
        $promotionDate = ! empty($validated['promotion_date']) ? $validated['promotion_date'] : now()->toDateString();
        $studentIds = $validated['student_ids'];
        $promotedCount = count($studentIds);
        $targetClass = null;

        DB::transaction(function () use ($validated, $sourceClass, $promotionDate, $studentIds, &$targetClass) {
            if ($validated['action_type'] === 'graduate') {
                // Proses Kelulusan Siswa (Tingkat XII)
                foreach ($studentIds as $studentId) {
                    ClassEnrollment::where('class_id', $sourceClass->id)
                        ->where('student_id', $studentId)
                        ->where('status', 'ACTIVE')
                        ->update([
                            'status' => 'COMPLETED',
                            'end_date' => $promotionDate,
                        ]);

                    StudentProfile::where('id', $studentId)->update([
                        'status' => 'GRADUATED',
                        'graduation_date' => $promotionDate,
                    ]);

                    AlumniProfile::firstOrCreate(
                        ['student_id' => $studentId],
                        [
                            'graduation_year' => (int) date('Y', strtotime($promotionDate)),
                        ]
                    );
                }
            } else {
                // Proses Kenaikan Kelas (ke kelas existing atau otomatis buat kelas baru)
                if ($validated['action_type'] === 'create_new') {
                    $currentCode = $sourceClass->gradeLevel?->code;
                    $nextCode = match ($currentCode) {
                        'X' => 'XI',
                        'XI' => 'XII',
                        default => 'XII',
                    };
                    $nextLevel = GradeLevel::where('code', $nextCode)->first() ?? $sourceClass->gradeLevel;

                    $targetClass = SchoolClass::firstOrCreate(
                        [
                            'academic_year_id' => $validated['target_academic_year_id'],
                            'code' => $validated['new_class_code'],
                        ],
                        [
                            'name' => $validated['new_class_name'],
                            'department_id' => $sourceClass->department_id,
                            'grade_level_id' => $nextLevel->id,
                            'homeroom_teacher_id' => $validated['new_homeroom_teacher_id'] ?? null,
                            'is_active' => true,
                        ]
                    );
                } else {
                    $targetClass = SchoolClass::findOrFail($validated['target_class_id']);
                }

                foreach ($studentIds as $studentId) {
                    // Selesaikan status pendaftaran di rombel lama
                    ClassEnrollment::where('class_id', $sourceClass->id)
                        ->where('student_id', $studentId)
                        ->where('status', 'ACTIVE')
                        ->update([
                            'status' => 'COMPLETED',
                            'end_date' => $promotionDate,
                        ]);

                    // Daftarkan siswa ke rombel tujuan yang baru
                    ClassEnrollment::updateOrCreate(
                        [
                            'class_id' => $targetClass->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'start_date' => $promotionDate,
                            'end_date' => null,
                            'status' => 'ACTIVE',
                        ]
                    );

                    StudentProfile::where('id', $studentId)->update([
                        'status' => 'ACTIVE',
                    ]);
                }
            }

            if (! empty($validated['deactivate_source_class'])) {
                $sourceClass->update(['is_active' => false]);
            }
        });

        if ($validated['action_type'] === 'graduate') {
            $msg = ! empty($validated['deactivate_source_class'])
                ? "Proses kelulusan berhasil! {$promotedCount} siswa dari {$sourceClass->name} telah dinyatakan lulus dan rombel dinonaktifkan."
                : "Proses kelulusan berhasil! {$promotedCount} siswa dari {$sourceClass->name} telah dinyatakan lulus dan rombel kini dalam keadaan kosong (siap digunakan kembali).";

            return redirect()->route('admin.academic.classes.index')
                ->with('success', $msg);
        }

        $targetName = $targetClass?->name ?? 'kelas tujuan';

        return redirect()->route('admin.academic.classes.index')
            ->with('success', "Kenaikan kelas berhasil! {$promotedCount} siswa dari {$sourceClass->name} berhasil dinaikkan ke {$targetName}.");
    }
}
