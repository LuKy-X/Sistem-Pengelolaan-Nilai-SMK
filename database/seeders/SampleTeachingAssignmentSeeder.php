<?php

namespace Database\Seeders;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\AttendanceStatus;
use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Enums\LateReductionType;
use App\Models\Assessment;
use App\Models\AssessmentLatePolicy;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\Gradebook;
use App\Models\GradebookCategory;
use App\Models\GradebookColumn;
use App\Models\GradebookColumnSource;
use App\Models\GradebookScore;
use App\Models\GradebookStudent;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Illuminate\Database\Seeder;

class SampleTeachingAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teacherAgus = TeacherProfile::where('nip', '198001012005011001')->first();
        $teacherBudi = TeacherProfile::where('nip', '198202022006021002')->first();

        $classXiiRpl1 = SchoolClass::where('code', 'XII-RPL-1')->first();
        $classXiiRpl2 = SchoolClass::where('code', 'XII-RPL-2')->first();
        $classXiRpl1 = SchoolClass::where('code', 'XI-RPL-1')->first();

        $mapelMtk = Subject::where('code', 'MTK')->first();
        $mapelPwpb = Subject::where('code', 'PWPB')->first();
        $mapelBd = Subject::where('code', 'BD')->first();
        $mapelBin = Subject::where('code', 'BIN')->first();

        $semesterGanjil = Semester::where('semester_number', 1)->first();

        if (! $teacherAgus || ! $classXiiRpl1 || ! $semesterGanjil) {
            return;
        }

        // Pastikan guru hanya mengajar 1 mata pelajaran (Matematika)
        TeachingAssignment::where('teacher_id', $teacherAgus->id)
            ->where('subject_id', '!=', $mapelMtk->id)
            ->delete();

        // 1. Assignment Guru Agus: Matematika XII RPL 1
        $assign1 = TeachingAssignment::firstOrCreate(
            [
                'teacher_id' => $teacherAgus->id,
                'subject_id' => $mapelMtk->id,
                'class_id' => $classXiiRpl1->id,
                'semester_id' => $semesterGanjil->id,
            ],
            ['weekly_hours' => 4, 'is_active' => true]
        );

        // 2. Assignment Guru Agus: Matematika XII RPL 2
        $assign2 = TeachingAssignment::firstOrCreate(
            [
                'teacher_id' => $teacherAgus->id,
                'subject_id' => $mapelMtk->id,
                'class_id' => $classXiiRpl2->id,
                'semester_id' => $semesterGanjil->id,
            ],
            ['weekly_hours' => 4, 'is_active' => true]
        );

        // 3. Assignment Guru Agus: Matematika XI RPL 1
        $assign3 = TeachingAssignment::firstOrCreate(
            [
                'teacher_id' => $teacherAgus->id,
                'subject_id' => $mapelMtk->id,
                'class_id' => $classXiRpl1->id,
                'semester_id' => $semesterGanjil->id,
            ],
            ['weekly_hours' => 4, 'is_active' => true]
        );

        // 5. Assignment Guru Budi: Bahasa Indonesia XII RPL 1
        $assign5 = TeachingAssignment::firstOrCreate(
            [
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $mapelBin->id,
                'class_id' => $classXiiRpl1->id,
                'semester_id' => $semesterGanjil->id,
            ],
            ['weekly_hours' => 2, 'is_active' => true]
        );

        // BUILD A REAL GRADEBOOK FOR GURU AGUS (Matematika XII RPL 1)
        $gradebook = Gradebook::firstOrCreate(
            ['teaching_assignment_id' => $assign1->id, 'name' => 'Buku Nilai Matematika XII RPL 1'],
            ['description' => 'Buku nilai utama semester ganjil tahun ajaran 2025/2026', 'is_active' => true]
        );

        // Enrolled students into gradebook
        $enrollments = ClassEnrollment::where('class_id', $classXiiRpl1->id)->get();
        foreach ($enrollments as $enrollment) {
            GradebookStudent::firstOrCreate(
                ['gradebook_id' => $gradebook->id, 'student_id' => $enrollment->student_id],
                ['class_enrollment_id' => $enrollment->id, 'status' => 'ACTIVE', 'joined_at' => now()]
            );
        }

        // Categories
        $catUH = GradebookCategory::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'UH'],
            ['name' => 'Ulangan Harian', 'weight' => 30.00, 'sort_order' => 1, 'is_included_in_average' => true]
        );

        $catTugas = GradebookCategory::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'TUGAS'],
            ['name' => 'Tugas & Portofolio', 'weight' => 20.00, 'sort_order' => 2, 'is_included_in_average' => true]
        );

        // Columns: UH 1 & UH 2
        $colUH1 = GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'UH1'],
            [
                'category_id' => $catUH->id,
                'name' => 'Ulangan Harian 1',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 15.00,
                'sort_order' => 1,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        $colUH2 = GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'UH2'],
            [
                'category_id' => $catUH->id,
                'name' => 'Ulangan Harian 2',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 15.00,
                'sort_order' => 2,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        // Column: Summary (Rata-rata UH)
        $colAvgUH = GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'AVG_UH'],
            [
                'category_id' => $catUH->id,
                'name' => 'Rata-rata Ulangan Harian',
                'column_type' => GradebookColumnType::Summary,
                'calculation_type' => GradebookCalculationType::Average,
                'max_score' => 100.00,
                'weight' => 30.00,
                'sort_order' => 3,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        // Sources for summary
        GradebookColumnSource::firstOrCreate(
            ['summary_column_id' => $colAvgUH->id, 'source_column_id' => $colUH1->id],
            ['weight' => 1.00]
        );
        GradebookColumnSource::firstOrCreate(
            ['summary_column_id' => $colAvgUH->id, 'source_column_id' => $colUH2->id],
            ['weight' => 1.00]
        );

        // Column: Tugas 1
        $colTugas1 = GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook->id, 'code' => 'TGS1'],
            [
                'category_id' => $catTugas->id,
                'name' => 'Tugas 1 - Matriks & Transformasi',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 20.00,
                'sort_order' => 4,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        // Assessment linked to Tugas 1
        $assessment = Assessment::firstOrCreate(
            ['gradebook_column_id' => $colTugas1->id],
            [
                'teaching_assignment_id' => $assign1->id,
                'type' => AssessmentType::Task,
                'title' => 'Tugas 1 - Operasi Matriks dan Determinan',
                'description' => 'Kerjakan soal latihan matriks halaman 45-48 buku paket.',
                'instructions' => 'Ketikkan penjelasan langkah pengerjaan atau upload scan/foto lembar jawaban.',
                'due_at' => now()->addDays(5),
                'submission_required' => true,
                'created_by' => $teacherAgus->id,
                'published_at' => now(),
                'status' => AssessmentStatus::Published,
            ]
        );

        AssessmentLatePolicy::firstOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'enabled' => true,
                'reduction_type' => LateReductionType::Percentage,
                'reduction_value' => 5.00,
                'interval' => 60,
                'grace_period_minutes' => 15,
                'minimum_max_score' => 50.00,
            ]
        );

        // Populate sample scores for students in XII RPL 1
        foreach ($enrollments as $enrollment) {
            GradebookScore::firstOrCreate(
                ['gradebook_column_id' => $colUH1->id, 'student_id' => $enrollment->student_id],
                [
                    'raw_score' => 88.00,
                    'final_score' => 88.00,
                    'max_score_snapshot' => 100.00,
                    'late_minutes' => 0,
                    'late_deduction' => 0.00,
                    'source' => 'MANUAL',
                    'graded_by' => $teacherAgus->id,
                    'graded_at' => now(),
                ]
            );

            GradebookScore::firstOrCreate(
                ['gradebook_column_id' => $colUH2->id, 'student_id' => $enrollment->student_id],
                [
                    'raw_score' => 92.00,
                    'final_score' => 92.00,
                    'max_score_snapshot' => 100.00,
                    'late_minutes' => 0,
                    'late_deduction' => 0.00,
                    'source' => 'MANUAL',
                    'graded_by' => $teacherAgus->id,
                    'graded_at' => now(),
                ]
            );
        }

        // BUILD A REAL GRADEBOOK FOR GURU AGUS (Matematika XII RPL 2)
        $gradebook2 = Gradebook::firstOrCreate(
            ['teaching_assignment_id' => $assign2->id, 'name' => 'Buku Nilai Matematika XII RPL 2'],
            ['description' => 'Buku nilai utama semester ganjil tahun ajaran 2026/2027', 'is_active' => true]
        );
        $enrollments2 = ClassEnrollment::where('class_id', $classXiiRpl2->id)->get();
        foreach ($enrollments2 as $enrollment) {
            GradebookStudent::firstOrCreate(
                ['gradebook_id' => $gradebook2->id, 'student_id' => $enrollment->student_id],
                ['class_enrollment_id' => $enrollment->id, 'status' => 'ACTIVE', 'joined_at' => now()]
            );
        }
        $catUH2 = GradebookCategory::firstOrCreate(
            ['gradebook_id' => $gradebook2->id, 'code' => 'UH'],
            ['name' => 'Ulangan Harian', 'weight' => 50.00, 'sort_order' => 1, 'is_included_in_average' => true]
        );
        GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook2->id, 'code' => 'UH1'],
            [
                'category_id' => $catUH2->id,
                'name' => 'Ulangan Harian 1',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 25.00,
                'sort_order' => 1,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );
        GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook2->id, 'code' => 'UH2'],
            [
                'category_id' => $catUH2->id,
                'name' => 'Ulangan Harian 2',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 25.00,
                'sort_order' => 2,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        // BUILD A REAL GRADEBOOK FOR GURU AGUS (Matematika XI RPL 1)
        $gradebook3 = Gradebook::firstOrCreate(
            ['teaching_assignment_id' => $assign3->id, 'name' => 'Buku Nilai Matematika XI RPL 1'],
            ['description' => 'Buku nilai utama semester ganjil tahun ajaran 2026/2027', 'is_active' => true]
        );
        $enrollments3 = ClassEnrollment::where('class_id', $classXiRpl1->id)->get();
        foreach ($enrollments3 as $enrollment) {
            GradebookStudent::firstOrCreate(
                ['gradebook_id' => $gradebook3->id, 'student_id' => $enrollment->student_id],
                ['class_enrollment_id' => $enrollment->id, 'status' => 'ACTIVE', 'joined_at' => now()]
            );
        }
        $catUH3 = GradebookCategory::firstOrCreate(
            ['gradebook_id' => $gradebook3->id, 'code' => 'UH'],
            ['name' => 'Ulangan Harian', 'weight' => 50.00, 'sort_order' => 1, 'is_included_in_average' => true]
        );
        GradebookColumn::firstOrCreate(
            ['gradebook_id' => $gradebook3->id, 'code' => 'UH1'],
            [
                'category_id' => $catUH3->id,
                'name' => 'Ulangan Harian 1',
                'column_type' => GradebookColumnType::Score,
                'max_score' => 100.00,
                'weight' => 25.00,
                'sort_order' => 1,
                'is_visible' => true,
                'is_included_in_average' => true,
            ]
        );

        // SAMPLE CLASS JOURNALS & ATTENDANCE FOR XII-RPL-1
        $period1 = LessonPeriod::where('period_number', 1)->first();
        $period2 = LessonPeriod::where('period_number', 2)->first();
        $period3 = LessonPeriod::where('period_number', 3)->first();
        $period4 = LessonPeriod::where('period_number', 4)->first();

        // Assignment Guru Budi: Bahasa Indonesia XII RPL 1
        $assignBudi = null;
        if ($teacherBudi && $mapelBin) {
            $assignBudi = TeachingAssignment::firstOrCreate(
                [
                    'teacher_id' => $teacherBudi->id,
                    'subject_id' => $mapelBin->id,
                    'class_id' => $classXiiRpl1->id,
                    'semester_id' => $semesterGanjil->id,
                ],
                ['weekly_hours' => 2, 'is_active' => true]
            );
        }

        // Journal Jam 1-2 Bahasa Indonesia (Guru Budi)
        if ($assignBudi && $period1 && $period2) {
            $journalBin = ClassJournal::firstOrCreate(
                [
                    'teaching_assignment_id' => $assignBudi->id,
                    'journal_date' => now()->format('Y-m-d'),
                    'start_period_id' => $period1->id,
                    'end_period_id' => $period2->id,
                ],
                [
                    'material' => 'Menganalisis Kaidah Kebahasaan dan Struktur Teks Editorial',
                    'notes' => 'Diskusi kelompok aktif. Materi tuntas disampaikan.',
                    'created_by' => $teacherBudi->id,
                ]
            );

            // Students: Ahmad Fajar (Izin), Siti Nurhaliza (Sakit)
            $firstStudent = ClassEnrollment::where('class_id', $classXiiRpl1->id)->orderBy('id')->first()?->student;
            $secondStudent = ClassEnrollment::where('class_id', $classXiiRpl1->id)->orderBy('id')->skip(1)->first()?->student;

            if ($firstStudent) {
                JournalAttendance::firstOrCreate(
                    ['journal_id' => $journalBin->id, 'student_id' => $firstStudent->id],
                    ['status' => AttendanceStatus::Permit, 'note' => 'Izin keperluan keluarga']
                );
            }
            if ($secondStudent) {
                JournalAttendance::firstOrCreate(
                    ['journal_id' => $journalBin->id, 'student_id' => $secondStudent->id],
                    ['status' => AttendanceStatus::Sick, 'note' => 'Sakit demam']
                );
            }
        }

        // Previous Journal Jam 3-4 Matematika (Guru Agus) on yesterday
        if ($assign1 && $period3 && $period4) {
            $journalMtk = ClassJournal::firstOrCreate(
                [
                    'teaching_assignment_id' => $assign1->id,
                    'journal_date' => now()->subDay()->format('Y-m-d'),
                    'start_period_id' => $period3->id,
                    'end_period_id' => $period4->id,
                ],
                [
                    'material' => 'Sistem Persamaan Linear Dua Variabel (SPLDV) dan Matriks',
                    'notes' => 'Latihan soal mandiri berjalan tertib.',
                    'created_by' => $teacherAgus->id,
                ]
            );

            $firstStudent = ClassEnrollment::where('class_id', $classXiiRpl1->id)->orderBy('id')->first()?->student;
            if ($firstStudent) {
                JournalAttendance::firstOrCreate(
                    ['journal_id' => $journalMtk->id, 'student_id' => $firstStudent->id],
                    ['status' => AttendanceStatus::Permit, 'note' => 'Izin dispensasi OSIS']
                );
            }
        }

        // SEED TEACHING SCHEDULES FOR GURU AGUS
        // 1. Matematika XII RPL 1: Kamis (Day 4), Jam 3 - 4
        if ($assign1 && $period3 && $period4) {
            TeachingSchedule::firstOrCreate(
                [
                    'teaching_assignment_id' => $assign1->id,
                    'day_of_week' => 4, // Kamis
                ],
                [
                    'start_period_id' => $period3->id,
                    'end_period_id' => $period4->id,
                    'room' => 'R. Lab RPL 1',
                ]
            );
        }

        // 2. Matematika XII RPL 2: Jumat (Day 5 - Hari Ini!), Jam 1 - 4
        if ($assign2 && $period1 && $period4) {
            TeachingSchedule::firstOrCreate(
                [
                    'teaching_assignment_id' => $assign2->id,
                    'day_of_week' => 5, // Jumat
                ],
                [
                    'start_period_id' => $period1->id,
                    'end_period_id' => $period4->id,
                    'room' => 'R. Lab RPL 2',
                ]
            );
        }

        // 3. Matematika XI RPL 1: Rabu (Day 3 - Terlewat/Belum Diisi), Jam 1 - 4
        if ($assign3 && $period1 && $period4) {
            TeachingSchedule::firstOrCreate(
                [
                    'teaching_assignment_id' => $assign3->id,
                    'day_of_week' => 3, // Rabu
                ],
                [
                    'start_period_id' => $period1->id,
                    'end_period_id' => $period4->id,
                    'room' => 'R. XI RPL 1',
                ]
            );
        }
    }
}
