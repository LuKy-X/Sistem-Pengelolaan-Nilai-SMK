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

class SampleClassSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first();
        $rpl = Department::where('code', 'RPL')->first();
        $level12 = GradeLevel::where('code', 'XII')->first();
        $level11 = GradeLevel::where('code', 'XI')->first();
        $teacherAgus = TeacherProfile::where('nip', '198001012005011001')->first();
        $teacherBudi = TeacherProfile::where('nip', '198202022006021002')->first();

        // 1. XII RPL 1
        $class1 = SchoolClass::firstOrCreate(
            ['academic_year_id' => $year->id, 'code' => 'XII-RPL-1'],
            [
                'department_id' => $rpl->id,
                'grade_level_id' => $level12->id,
                'homeroom_teacher_id' => $teacherAgus?->id,
                'name' => 'XII Rekayasa Perangkat Lunak 1',
                'is_active' => true,
            ]
        );

        // 2. XII RPL 2
        $class2 = SchoolClass::firstOrCreate(
            ['academic_year_id' => $year->id, 'code' => 'XII-RPL-2'],
            [
                'department_id' => $rpl->id,
                'grade_level_id' => $level12->id,
                'homeroom_teacher_id' => $teacherBudi?->id,
                'name' => 'XII Rekayasa Perangkat Lunak 2',
                'is_active' => true,
            ]
        );

        // 3. XI RPL 1
        $class3 = SchoolClass::firstOrCreate(
            ['academic_year_id' => $year->id, 'code' => 'XI-RPL-1'],
            [
                'department_id' => $rpl->id,
                'grade_level_id' => $level11->id,
                'homeroom_teacher_id' => null,
                'name' => 'XI Rekayasa Perangkat Lunak 1',
                'is_active' => true,
            ]
        );

        // Enroll students
        $siswaAhmad = StudentProfile::where('nis', '10001')->first();
        $siswaSiti = StudentProfile::where('nis', '10002')->first();
        $siswaRizky = StudentProfile::where('nis', '10003')->first();

        if ($siswaAhmad) {
            ClassEnrollment::firstOrCreate(
                ['class_id' => $class1->id, 'student_id' => $siswaAhmad->id],
                ['start_date' => '2025-07-15', 'status' => 'ACTIVE']
            );
        }

        if ($siswaSiti) {
            ClassEnrollment::firstOrCreate(
                ['class_id' => $class1->id, 'student_id' => $siswaSiti->id],
                ['start_date' => '2025-07-15', 'status' => 'ACTIVE']
            );
        }

        if ($siswaRizky) {
            ClassEnrollment::firstOrCreate(
                ['class_id' => $class2->id, 'student_id' => $siswaRizky->id],
                ['start_date' => '2025-07-15', 'status' => 'ACTIVE']
            );
        }
    }
}
