<?php

namespace Tests\Feature;

use App\Enums\AppealDecision;
use App\Enums\DisciplinaryLetterType;
use App\Enums\DisciplineCategoryType;
use App\Enums\ExitPermitStatus;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\ExitPermitReason;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stress test batas akses data Guru BK.
 *
 * Setiap Guru BK hanya boleh menangani siswa yang berada di kelas binaannya
 * (relasi `counselor_class`). Modul lain seperti Poin Disiplin dan Rekam Konseling
 * sudah menerapkan scoping ini; test di sini mengunci perilaku tersebut untuk
 * seluruh modul BK dan memastikan tidak ada celah IDOR.
 */
class CounselorAccessBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $counselor;

    /**
     * Kelas yang diampu BK.
     */
    protected SchoolClass $counseledClass;

    /**
     * Kelas milik sekolah lain yang tidak boleh disentuh BK.
     */
    protected SchoolClass $foreignClass;

    protected StudentProfile $counseledStudent;

    protected StudentProfile $foreignStudent;

    protected int $academicYearId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->counselor = User::where('username', 'bk.dewi')->firstOrFail();
        $this->academicYearId = (int) AcademicYear::where('is_active', true)->value('id');

        // Seeder示例 memberi BK seluruh kelas sekolah; untuk test ini dibatasi
        // menjadi satu kelas agar batas aksesnya benar-benar teruji.
        $classes = SchoolClass::where('is_active', true)->orderBy('id')->get();
        $this->foreignClass = $classes->firstOrFail();
        $this->counseledClass = $classes->last();

        $this->counselor->counseledClasses()->sync([$this->counseledClass->id]);

        $this->counseledStudent = $this->activeStudentOf($this->counseledClass);
        $this->foreignStudent = $this->activeStudentOf($this->foreignClass);
    }

    // ────────────────────────────────────────────────────────────────────
    // Surat Peringatan
    // ────────────────────────────────────────────────────────────────────

    public function test_counselor_cannot_issue_letter_for_student_outside_counseled_classes(): void
    {
        $this->actingAs($this->counselor)
            ->post(route('counselor.disciplinary-letters.store'), [
                'student_id' => $this->foreignStudent->id,
                'type' => DisciplinaryLetterType::Sp3->value,
                'reason' => 'Meninggalkan sekolah tanpa izin selama tiga hari berturut-turut.',
                'issued_at' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseMissing('disciplinary_letters', [
            'student_id' => $this->foreignStudent->id,
        ]);
    }

    public function test_counselor_cannot_open_letter_of_student_outside_counseled_classes(): void
    {
        $letter = $this->letterFor($this->foreignStudent);

        $this->actingAs($this->counselor)
            ->get(route('counselor.disciplinary-letters.show', $letter))
            ->assertForbidden();
    }

    public function test_counselor_cannot_delete_letter_of_student_outside_counseled_classes(): void
    {
        $letter = $this->letterFor($this->foreignStudent);

        $this->actingAs($this->counselor)
            ->delete(route('counselor.disciplinary-letters.destroy', $letter))
            ->assertForbidden();

        $this->assertDatabaseHas('disciplinary_letters', ['id' => $letter->id]);
    }

    public function test_counselor_can_still_manage_letters_of_counseled_students(): void
    {
        $letter = $this->letterFor($this->counseledStudent);

        $this->actingAs($this->counselor)
            ->get(route('counselor.disciplinary-letters.show', $letter))
            ->assertOk()
            ->assertSee($this->counseledStudent->full_name);
    }

    // ────────────────────────────────────────────────────────────────────
    // Poin Disiplin
    // ────────────────────────────────────────────────────────────────────

    public function test_counselor_cannot_delete_discipline_record_of_uncounseled_student(): void
    {
        $record = DisciplineRecord::create([
            'student_id' => $this->foreignStudent->id,
            'academic_year_id' => $this->academicYearId,
            'category_id' => DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->value('id'),
            'points_delta' => -40,
            'occurred_at' => now(),
            'description' => 'Meninggalkan kelas tanpa izin.',
            'source_type' => 'MANUAL',
            'created_by' => $this->counselor->id,
        ]);

        $this->actingAs($this->counselor)
            ->delete(route('counselor.discipline.destroy', $record))
            ->assertForbidden();

        $this->assertDatabaseHas('discipline_records', ['id' => $record->id]);
    }

    public function test_student_scopes_filter_by_discipline_standing(): void
    {
        $setting = DisciplineSetting::where('academic_year_id', $this->academicYearId)->firstOrFail();

        DisciplineRecord::query()->delete();

        $makeRecord = function (StudentProfile $student, int $delta): void {
            DisciplineRecord::create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYearId,
                'category_id' => DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->value('id'),
                'points_delta' => $delta,
                'occurred_at' => now(),
                'description' => 'Pelanggaran untuk uji cakupan.',
                'source_type' => 'MANUAL',
                'created_by' => $this->counselor->id,
            ]);
        };

        // Tiga siswa dengan kondisi ambang yang berbeda.
        $safe = $this->counseledStudent;
        $sp1Student = $this->otherCounseledStudent();
        $sp3Student = $this->anotherCounseledStudent();

        $makeRecord($sp1Student, -((int) $setting->initial_points - (int) $setting->sp1_threshold));
        $makeRecord($sp3Student, -((int) $setting->initial_points - (int) $setting->sp3_threshold));

        $visibleIds = function (string $scope): array {
            $response = $this->actingAs($this->counselor)
                ->get(route('counselor.students.index', ['scope' => $scope]))
                ->assertOk();

            preg_match_all('/\/bk\/students\/(\d+)/', (string) $response->getContent(), $matches);

            return array_map('intval', array_unique($matches[1] ?? []));
        };

        $sp1Ids = $visibleIds('sp1');
        $this->assertContains($sp1Student->id, $sp1Ids);
        $this->assertNotContains($sp3Student->id, $sp1Ids);
        $this->assertNotContains($safe->id, $sp1Ids);

        $sp23Ids = $visibleIds('sp23');
        $this->assertContains($sp3Student->id, $sp23Ids);
        $this->assertNotContains($sp1Student->id, $sp23Ids);

        $attentionIds = $visibleIds('attention');
        $this->assertContains($sp1Student->id, $attentionIds);
        $this->assertContains($sp3Student->id, $attentionIds);
        $this->assertNotContains($safe->id, $attentionIds);
    }

    public function test_unknown_scope_falls_back_to_all_students(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.students.index', ['scope' => 'hacker']))
            ->assertOk()
            ->assertSee($this->counseledStudent->full_name);
    }

    public function test_discipline_index_never_lists_uncounseled_students(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.discipline.index'))
            ->assertOk()
            ->assertDontSee($this->foreignStudent->full_name);
    }

    // ────────────────────────────────────────────────────────────────────
    // Rekam Konseling
    // ────────────────────────────────────────────────────────────────────

    public function test_counseling_class_filter_cannot_reach_uncounseled_class(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.counseling.index', ['class_id' => $this->foreignClass->id]))
            ->assertOk()
            ->assertDontSee($this->foreignStudent->full_name);
    }

    public function test_counseling_index_ignores_unknown_class_filter(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.counseling.index', ['class_id' => 999999]))
            ->assertOk()
            ->assertDontSee($this->foreignStudent->full_name);
    }

    // ────────────────────────────────────────────────────────────────────
    // Banding Izin Keluar
    // ────────────────────────────────────────────────────────────────────

    public function test_accepted_appeal_never_records_a_discipline_sanction(): void
    {
        $appeal = $this->pendingAppealFor($this->counseledStudent);
        $categoryId = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->value('id');

        // Percobaan mencatat sanksi bersamaan dengan keputusan "diterima" harus ditolak.
        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Accepted->value,
                'record_sanction' => 1,
                'sanction_category_id' => $categoryId,
                'sanction_points' => 30,
            ])
            ->assertSessionHasErrors('record_sanction');

        $this->assertDatabaseHas('exit_permit_appeals', [
            'id' => $appeal->id,
            'decision' => AppealDecision::Pending->value,
        ]);

        $this->assertDatabaseMissing('discipline_records', [
            'source_type' => 'APPEAL',
            'source_id' => $appeal->id,
        ]);

        // Banding diterima tanpa sanksi → tetap diterima, tanpa poin.
        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Accepted->value,
            ])
            ->assertRedirect(route('counselor.appeals.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exit_permit_appeals', [
            'id' => $appeal->id,
            'decision' => AppealDecision::Accepted->value,
        ]);

        $this->assertDatabaseMissing('discipline_records', [
            'source_type' => 'APPEAL',
            'source_id' => $appeal->id,
        ]);
    }

    public function test_rejected_appeal_cannot_use_reward_category_to_increase_points(): void
    {
        $appeal = $this->pendingAppealFor($this->counseledStudent);
        $rewardCategory = DisciplineCategory::where('type', DisciplineCategoryType::Reward->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
                'decision_note' => 'Banding ditolak karena bukti tidak memadai.',
                'record_sanction' => 1,
                'sanction_category_id' => $rewardCategory->id,
                'sanction_points' => 50,
            ])
            ->assertSessionHasErrors('sanction_category_id');

        $this->assertDatabaseHas('exit_permit_appeals', [
            'id' => $appeal->id,
            'decision' => AppealDecision::Pending->value,
        ]);

        $this->assertDatabaseMissing('discipline_records', [
            'source_type' => 'APPEAL',
            'source_id' => $appeal->id,
        ]);
    }

    public function test_rejected_appeal_records_negative_sanction_for_violation(): void
    {
        $appeal = $this->pendingAppealFor($this->counseledStudent);
        $categoryId = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->value('id');

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
                'decision_note' => 'Banding ditolak karena bukti tidak memadai.',
                'record_sanction' => 1,
                'sanction_category_id' => $categoryId,
                'sanction_points' => 30,
            ]);

        $this->assertDatabaseHas('discipline_records', [
            'source_type' => 'APPEAL',
            'source_id' => $appeal->id,
            'points_delta' => -30,
        ]);
    }

    public function test_appeal_decision_cannot_be_replayed_after_it_is_final(): void
    {
        $appeal = $this->pendingAppealFor($this->counseledStudent);
        $categoryId = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->value('id');

        $payload = [
            'decision' => AppealDecision::Rejected->value,
            'decision_note' => 'Banding ditolak karena bukti tidak memadai.',
            'record_sanction' => 1,
            'sanction_category_id' => $categoryId,
            'sanction_points' => 25,
        ];

        $this->actingAs($this->counselor)->post(route('counselor.appeals.decide', $appeal), $payload);
        $this->actingAs($this->counselor)->post(route('counselor.appeals.decide', $appeal), $payload);

        $this->assertSame(1, DisciplineRecord::where('source_type', 'APPEAL')
            ->where('source_id', $appeal->id)
            ->count());
    }

    // ────────────────────────────────────────────────────────────────────
    // Data Siswa
    // ────────────────────────────────────────────────────────────────────

    public function test_counselor_cannot_open_profile_of_uncounseled_student(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.students.show', $this->foreignStudent))
            ->assertForbidden();
    }

    public function test_student_card_links_point_to_the_expected_pages(): void
    {
        // Kartu siswa, tautan kembali, dan KPI harus menuju halaman yang benar.
        foreach (['all', 'attention', 'sp1', 'sp23'] as $kpiScope) {
            $kpiHtml = (string) $this->actingAs($this->counselor)
                ->get(route('counselor.students.index', ['scope' => $kpiScope]))
                ->assertOk()
                ->getContent();

            $this->assertMatchesRegularExpression(
                '/<a href="'.preg_quote(route('counselor.students.index', ['scope' => $kpiScope]), '/').'"/',
                $kpiHtml
            );
        }

        $indexHtml = $this->actingAs($this->counselor)
            ->get(route('counselor.students.index'))
            ->assertOk()
            ->getContent();

        $cardAnchor = substr(
            (string) preg_replace(
                '/\s+/',
                ' ',
                (string) strstr((string) $indexHtml, '<a href="'.route('counselor.students.show', $this->counseledStudent).'"')
            ),
            0,
            120
        );

        $this->assertStringContainsString(route('counselor.students.show', $this->counseledStudent), $cardAnchor);

        $showHtml = (string) $this->actingAs($this->counselor)
            ->get(route('counselor.students.show', $this->counseledStudent))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<a href="'.preg_quote(route('counselor.students.index'), '/').'"/',
            $showHtml
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // Ketahanan input
    // ────────────────────────────────────────────────────────────────────

    public function test_invalid_date_filters_do_not_break_pages(): void
    {
        foreach ([
            route('counselor.journals.attendance', ['date' => 'bukan-tanggal']),
            route('counselor.journals.history', ['date_from' => 'xx', 'date_to' => 'yy']),
            route('counselor.exit-permits.index', ['status' => 'DROP TABLE']),
            route('counselor.appeals.index', ['decision' => '<script>']),
            route('counselor.discipline.index', ['date_from' => 'invalid', 'date_to' => 'invalid']),
            route('counselor.disciplinary-letters.index', ['q' => '%00%']),
        ] as $url) {
            $this->actingAs($this->counselor)->get($url)->assertOk();
        }
    }

    public function test_massive_query_payloads_are_handled(): void
    {
        $long = str_repeat('a', 4000);

        foreach ([
            route('counselor.journals.index', ['date' => $long]),
            route('counselor.discipline.index', ['q' => $long]),
            route('counselor.exit-permits.index', ['q' => $long]),
            route('counselor.students.index', ['q' => $long]),
        ] as $url) {
            $this->actingAs($this->counselor)->get($url)->assertOk();
        }
    }

    public function test_discipline_still_works_when_counselor_has_no_counseled_classes(): void
    {
        $this->counselor->counseledClasses()->detach();

        $this->actingAs($this->counselor)
            ->get(route('counselor.discipline.index'))
            ->assertOk();

        $this->actingAs($this->counselor)
            ->post(route('counselor.disciplinary-letters.store'), [
                'student_id' => $this->counseledStudent->id,
                'type' => DisciplinaryLetterType::Sp1->value,
                'reason' => 'Terlambat masuk sekolah berulang kali.',
                'issued_at' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('student_id');
    }

    // ────────────────────────────────────────────────────────────────────
    // Helper
    // ────────────────────────────────────────────────────────────────────

    protected function activeStudentOf(SchoolClass $schoolClass): StudentProfile
    {
        return ClassEnrollment::query()
            ->where('class_id', $schoolClass->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail()
            ->student;
    }

    protected function otherCounseledStudent(): StudentProfile
    {
        return ClassEnrollment::query()
            ->where('class_id', $this->counseledClass->id)
            ->where('status', 'ACTIVE')
            ->where('student_id', '!=', $this->counseledStudent->id)
            ->firstOrFail()
            ->student;
    }

    protected function anotherCounseledStudent(): StudentProfile
    {
        return ClassEnrollment::query()
            ->where('class_id', $this->counseledClass->id)
            ->where('status', 'ACTIVE')
            ->whereNotIn('student_id', [$this->counseledStudent->id, $this->otherCounseledStudent()->id])
            ->firstOrFail()
            ->student;
    }

    protected function letterFor(StudentProfile $student): DisciplinaryLetter
    {
        return DisciplinaryLetter::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYearId,
            'type' => DisciplinaryLetterType::Sp1->value,
            'reason' => 'Terlambat masuk sekolah selama tiga kali berturut-turut.',
            'issued_at' => now()->subDay(),
            'issued_by' => $this->counselor->id,
            'status' => 'ACTIVE',
        ]);
    }

    protected function pendingAppealFor(StudentProfile $student): ExitPermitAppeal
    {
        $permit = ExitPermit::create([
            'student_id' => $student->id,
            'reason_id' => ExitPermitReason::firstOrFail()->id,
            'reason_detail' => 'Izin ke rumah sakit untuk pemeriksaan '.random_int(1000, 9999).'.',
            'requested_at' => now()->subHours(3),
            'planned_exit_at' => now()->subHours(2),
            'planned_return_at' => now()->subHour(),
            'actual_exit_at' => now()->subHours(2),
            'actual_return_at' => now()->subMinutes(30),
            'status' => ExitPermitStatus::Late,
            'approved_at' => now()->subHours(2),
        ]);

        return ExitPermitAppeal::create([
            'exit_permit_id' => $permit->id,
            'submitted_at' => now()->subDay(),
            'reason' => 'Siswa sempat terjebak macet parah di jalan raya '.random_int(1000, 9999).'.',
        ]);
    }
}
