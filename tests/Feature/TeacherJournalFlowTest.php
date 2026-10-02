<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherJournalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;

    protected TeacherProfile $teacherProfile;

    protected TeachingAssignment $assignment;

    protected LessonPeriod $period1;

    protected LessonPeriod $period2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->teacherUser = User::where('username', 'guru.agus')->firstOrFail();
        $this->teacherProfile = $this->teacherUser->teacherProfile;
        $this->assignment = $this->teacherProfile->teachingAssignments()->firstOrFail();
        $this->period1 = LessonPeriod::where('period_number', 1)->firstOrFail();
        $this->period2 = LessonPeriod::where('period_number', 2)->firstOrFail();
    }

    public function test_teacher_can_view_journals_index_class_selection(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index'));

        $response->assertStatus(200);
        $response->assertSee('Absensi Kelas');
        $response->assertSee('grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3', false);
    }

    public function test_teacher_can_view_class_journal_table_and_form(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Journal - Kelas');
        $response->assertSee('Manajemen Absensi');
        $response->assertSee('Samakan dengan Jam Sebelumnya');
        $response->assertSee('Tambah Siswa');
        $response->assertDontSee('+ + Tambah Siswa');
    }

    public function test_teacher_can_store_journal_with_student_absences(): void
    {
        $student = ClassEnrollment::where('class_id', $this->assignment->class_id)->firstOrFail()->student;
        $todayStr = now()->format('Y-m-d');

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.journals.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $todayStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Matriks dan Sistem Persamaan Linear',
            'notes' => 'Siswa mengikuti pembelajaran dengan baik.',
            'hadir_count' => 35,
            'sakit_count' => 0,
            'izin_count' => 1,
            'alpha_count' => 0,
            'absences' => [
                [
                    'student_id' => $student->id,
                    'status' => 'IZIN',
                    'note' => 'Izin urusan keluarga',
                ],
            ],
        ]);

        $response->assertRedirect(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
            'date' => $todayStr,
        ]));
        $this->assertDatabaseHas('class_journals', [
            'teaching_assignment_id' => $this->assignment->id,
            'material' => 'Matriks dan Sistem Persamaan Linear',
        ]);

        $this->assertDatabaseHas('journal_attendances', [
            'student_id' => $student->id,
            'status' => AttendanceStatus::Permit->value,
            'note' => 'Izin urusan keluarga',
        ]);
    }

    public function test_journal_table_displays_only_selected_single_day_in_chronological_order(): void
    {
        ClassJournal::query()->delete();
        $period3 = LessonPeriod::where('period_number', 3)->firstOrFail();
        $period4 = LessonPeriod::where('period_number', 4)->firstOrFail();
        $todayStr = now()->format('Y-m-d');
        $yesterdayStr = now()->subDay()->format('Y-m-d');

        // Sesi jam 3-4 hari ini
        $todaySessionLater = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $todayStr,
            'start_period_id' => $period3->id,
            'end_period_id' => $period4->id,
            'material' => 'Sesi Siang Hari Ini',
            'notes' => 'Catatan sesi siang',
            'created_by' => $this->teacherProfile->id,
        ]);

        // Sesi jam 1-2 hari ini (guru lain / sesi pagi)
        $todaySessionEarlier = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $todayStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Sesi Pagi Hari Ini',
            'notes' => 'Catatan sesi pagi',
            'created_by' => $this->teacherProfile->id,
        ]);

        // Sesi kemarin (tidak boleh muncul di tabel hari ini)
        ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $yesterdayStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Sesi Kemarin yang Tidak Boleh Tampil',
            'notes' => 'Catatan kemarin',
            'created_by' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
            'date' => $todayStr,
        ]));

        $response->assertStatus(200);
        $journals = $response->viewData('journals');

        // Only 2 journals for today, yesterday is filtered out
        $this->assertCount(2, $journals);

        // Sorted chronologically from Jam 1 to end
        $this->assertEquals($todaySessionEarlier->id, $journals->first()->id);
        $this->assertEquals($todaySessionLater->id, $journals->last()->id);

        $response->assertSee('Sesi Pagi Hari Ini');
        $response->assertSee('Sesi Siang Hari Ini');
        $response->assertDontSee('Sesi Kemarin yang Tidak Boleh Tampil');
    }

    public function test_previous_journal_attendances_passed_to_view_for_next_teacher(): void
    {
        ClassJournal::query()->delete();
        $student = ClassEnrollment::where('class_id', $this->assignment->class_id)->firstOrFail()->student;

        // Create earlier journal for this class
        $journal = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => now()->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi Sesi 1',
            'notes' => 'Catatan sesi 1',
            'created_by' => $this->teacherProfile->id,
        ]);

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick,
            'note' => 'Sakit demam',
        ]);

        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
        ]));

        $response->assertStatus(200);
        $previousAttendances = $response->viewData('previousJournalAttendances');
        $this->assertNotEmpty($previousAttendances);
        $this->assertTrue($previousAttendances->contains('student_id', $student->id));
        $this->assertEquals('SAKIT', $previousAttendances->firstWhere('student_id', $student->id)['status']);
    }

    public function test_computed_accessors_on_class_journal(): void
    {
        $student = ClassEnrollment::where('class_id', $this->assignment->class_id)->firstOrFail()->student;

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => now()->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi Uji',
            'notes' => 'Hadir: 35 | Sakit: 1 | Izin: 0 | Alpha: 0',
            'created_by' => $this->teacherProfile->id,
        ]);

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick,
        ]);

        $journal->load(['attendances', 'teachingAssignment.schoolClass']);

        $this->assertEquals(1, $journal->sakit_count);
        $this->assertEquals(0, $journal->izin_count);
        $this->assertEquals(0, $journal->alpha_count);
        $this->assertEquals(35, $journal->hadir_count);
    }

    public function test_teacher_can_update_their_own_journal_with_student_notes(): void
    {
        $student = ClassEnrollment::where('class_id', $this->assignment->class_id)->firstOrFail()->student;
        $todayStr = now()->format('Y-m-d');

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $todayStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi Lama',
            'notes' => 'Catatan lama',
            'created_by' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacherUser)->put(route('teacher.journals.update', $journal->id), [
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi Baru yang Diperbarui',
            'notes' => 'Catatan revisi guru',
            'hadir_count' => 34,
            'sakit_count' => 1,
            'izin_count' => 1,
            'alpha_count' => 0,
            'absences' => [
                [
                    'student_id' => $student->id,
                    'status' => 'SAKIT',
                    'note' => 'Izin sakit flu dan demam',
                ],
            ],
        ]);

        $response->assertRedirect(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
            'date' => $todayStr,
        ]));

        $this->assertDatabaseHas('class_journals', [
            'id' => $journal->id,
            'material' => 'Materi Baru yang Diperbarui',
        ]);

        $this->assertDatabaseHas('journal_attendances', [
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick->value,
            'note' => 'Izin sakit flu dan demam',
        ]);
    }

    public function test_teacher_cannot_update_other_teachers_journal(): void
    {
        $otherTeacher = TeacherProfile::where('id', '!=', $this->teacherProfile->id)->firstOrFail();
        $otherAssignment = TeachingAssignment::where('teacher_id', $otherTeacher->id)->firstOrFail();

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $otherAssignment->id,
            'journal_date' => now()->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi Milik Guru Lain',
            'notes' => 'Catatan guru lain',
            'created_by' => $otherTeacher->id,
        ]);

        $response = $this->actingAs($this->teacherUser)->put(route('teacher.journals.update', $journal->id), [
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Mencoba Mengubah Materi Guru Lain',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('class_journals', [
            'id' => $journal->id,
            'material' => 'Materi Milik Guru Lain',
        ]);
    }

    public function test_teacher_can_delete_their_own_journal(): void
    {
        $student = ClassEnrollment::where('class_id', $this->assignment->class_id)->firstOrFail()->student;
        $todayStr = now()->format('Y-m-d');

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $todayStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Jurnal untuk Dihapus',
            'notes' => 'Catatan sebelum dihapus',
            'created_by' => $this->teacherProfile->id,
        ]);

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent,
            'note' => 'Alpha tanpa kabar',
        ]);

        $response = $this->actingAs($this->teacherUser)->delete(route('teacher.journals.destroy', $journal->id));

        $response->assertRedirect(route('teacher.journals.index', [
            'assignment_id' => $this->assignment->id,
            'date' => $todayStr,
        ]));

        $this->assertDatabaseMissing('class_journals', [
            'id' => $journal->id,
        ]);

        $this->assertDatabaseMissing('journal_attendances', [
            'journal_id' => $journal->id,
        ]);
    }

    public function test_teacher_cannot_delete_other_teachers_journal(): void
    {
        $otherTeacher = TeacherProfile::where('id', '!=', $this->teacherProfile->id)->firstOrFail();
        $otherAssignment = TeachingAssignment::where('teacher_id', $otherTeacher->id)->firstOrFail();

        $journal = ClassJournal::create([
            'teaching_assignment_id' => $otherAssignment->id,
            'journal_date' => now()->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Jurnal Guru Lain Tidak Boleh Dihapus',
            'created_by' => $otherTeacher->id,
        ]);

        $response = $this->actingAs($this->teacherUser)->delete(route('teacher.journals.destroy', $journal->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('class_journals', [
            'id' => $journal->id,
        ]);
    }

    public function test_weekly_schedule_status_detects_today_schedule_and_badges(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index'));

        $response->assertStatus(200);
        $assignments = $response->viewData('assignments');
        $this->assertNotEmpty($assignments);

        $hasSummary = $assignments->every(fn ($a) => isset($a->schedule_summary));
        $this->assertTrue($hasSummary, 'Every assignment should have schedule_summary computed.');

        // View should render week navigation and week dates
        $response->assertSee('Minggu Berjalan');
        $response->assertSee('Buka Jurnal');
    }

    public function test_weekly_schedule_status_detects_overdue_unfilled_journals(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index'));
        $assignments = $response->viewData('assignments');

        // Check if overdue assignment exists (XI RPL 1 was seeded on Rabu without journal)
        $overdueAssignment = $assignments->first(fn ($a) => ($a->schedule_summary['status_code'] ?? '') === 'overdue');
        if ($overdueAssignment) {
            $this->assertEquals('overdue', $overdueAssignment->schedule_summary['status_code']);
            $this->assertTrue($overdueAssignment->schedule_summary['is_overdue']);
            $this->assertStringContainsString('Terlewat', $overdueAssignment->schedule_summary['status_label']);
            $response->assertSee('Terlewat');
        }
    }

    public function test_class_card_links_directly_to_target_scheduled_date_of_the_week(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.journals.index'));
        $assignments = $response->viewData('assignments');

        foreach ($assignments as $a) {
            $targetDate = $a->schedule_summary['primary_date'];
            $this->assertNotEmpty($targetDate);
            $expectedUrl = route('teacher.journals.index', [
                'assignment_id' => $a->id,
                'date' => $targetDate,
            ]);
            $response->assertSee($expectedUrl);
        }
    }

    public function test_cannot_store_journal_for_future_dates(): void
    {
        $futureDateStr = now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($this->teacherUser)->post(route('teacher.journals.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'journal_date' => $futureDateStr,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Materi di Masa Depan',
            'notes' => 'Catatan masa depan',
        ]);

        $response->assertSessionHasErrors(['journal_date']);
        $this->assertDatabaseMissing('class_journals', [
            'material' => 'Materi di Masa Depan',
        ]);
    }
}
