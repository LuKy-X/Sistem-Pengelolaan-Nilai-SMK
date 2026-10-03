<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleClassSeeder extends Seeder
{
    /**
     * Import roster names from the three class attendance workbooks.
     */
    public function run(): void
    {
        $rosters = require __DIR__.'/data/student_rosters.php';
        $academicYear = AcademicYear::query()->where('is_active', true)->firstOrFail();
        $departments = Department::query()->whereIn('code', ['RPL', 'TKR', 'TPK', 'TPM'])->get()->keyBy('code');
        $gradeLevels = GradeLevel::query()->whereIn('code', ['X', 'XI', 'XII'])->get()->keyBy('code');
        $teacherAgus = TeacherProfile::query()->where('nip', '198001012005011001')->first();
        $teacherBudi = TeacherProfile::query()->where('nip', '198202022006021002')->first();

        DB::transaction(function () use ($academicYear, $departments, $gradeLevels, $rosters, $teacherAgus, $teacherBudi): void {
            foreach ($rosters as $gradeCode => $departmentRosters) {
                $gradeLevel = $gradeLevels->get($gradeCode);

                if ($gradeLevel === null) {
                    throw new \RuntimeException("Tingkat kelas {$gradeCode} belum tersedia.");
                }

                foreach ($departmentRosters as $departmentCode => $sections) {
                    $department = $departments->get($departmentCode);

                    if ($department === null) {
                        throw new \RuntimeException("Jurusan {$departmentCode} belum tersedia.");
                    }

                    foreach ($sections as $section => $studentNames) {
                        $classNumber = $section === '' ? 1 : ord($section) - ord('A') + 1;

                        if ($gradeCode === 'XI' && $departmentCode === 'TKR' && $section !== '') {
                            $classNumber++;
                        }

                        $classCode = "{$gradeCode}-{$departmentCode}-{$classNumber}";
                        $className = trim($gradeCode.' '.$department->short_name.' '.$section);
                        $homeroomTeacherId = match ($classCode) {
                            'XII-RPL-1' => $teacherAgus?->id,
                            'XII-RPL-2' => $teacherBudi?->id,
                            default => null,
                        };

                        $schoolClass = SchoolClass::updateOrCreate(
                            [
                                'academic_year_id' => $academicYear->id,
                                'code' => $classCode,
                            ],
                            [
                                'department_id' => $department->id,
                                'grade_level_id' => $gradeLevel->id,
                                'homeroom_teacher_id' => $homeroomTeacherId,
                                'name' => $className,
                                'is_active' => true,
                            ]
                        );

                        foreach ($studentNames as $index => $studentName) {
                            $studentNumber = $index + 1;
                            $nis = sprintf('SEED-%s-%s-%d-%02d', $gradeCode, $departmentCode, $classNumber, $studentNumber);
                            $nisn = sprintf('TMP-%s-%s-%d-%02d', $gradeCode, $departmentCode, $classNumber, $studentNumber);
                            $student = StudentProfile::firstOrNew(['nis' => $nis]);
                            $gender = $student->exists
                                ? $student->gender
                                : fake()->randomElement(['MALE', 'FEMALE']);

                            $student->fill([
                                'nisn' => $nisn,
                                'full_name' => $studentName,
                                'gender' => $gender,
                                'status' => 'ACTIVE',
                            ])->save();

                            ClassEnrollment::updateOrCreate(
                                [
                                    'class_id' => $schoolClass->id,
                                    'student_id' => $student->id,
                                ],
                                [
                                    'start_date' => $academicYear->start_date,
                                    'end_date' => null,
                                    'status' => 'ACTIVE',
                                ]
                            );
                        }
                    }
                }
            }

            // Keep the existing demo login accounts assigned to RPL classes.
            foreach ([
                ['nis' => '10001', 'class_code' => 'XII-RPL-1'],
                ['nis' => '10002', 'class_code' => 'XII-RPL-1'],
                ['nis' => '10003', 'class_code' => 'XII-RPL-2'],
            ] as $sampleEnrollment) {
                $student = StudentProfile::query()->where('nis', $sampleEnrollment['nis'])->first();
                $schoolClass = SchoolClass::query()
                    ->where('academic_year_id', $academicYear->id)
                    ->where('code', $sampleEnrollment['class_code'])
                    ->first();

                if ($student !== null && $schoolClass !== null) {
                    ClassEnrollment::firstOrCreate(
                        [
                            'class_id' => $schoolClass->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'start_date' => $academicYear->start_date,
                            'status' => 'ACTIVE',
                        ]
                    );
                }
            }
        });
    }
}
