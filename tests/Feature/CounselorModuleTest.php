<?php

namespace Tests\Feature;

use App\Enums\AppealDecision;
use App\Enums\DisciplinaryLetterType;
use App\Enums\DisciplineCategoryType;
use App\Enums\ExitPermitStatus;
use App\Models\AcademicYear;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\ExitPermitReason;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CounselorModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $counselor;

    protected StudentProfile $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->counselor = User::where('username', 'bk.dewi')->firstOrFail();
        $this->student = StudentProfile::query()
            ->whereHas('currentEnrollment')
            ->firstOrFail();
    }

    public function test_counselor_is_redirected_to_bk_dashboard_after_login(): void
    {
        $this->post('/login', [
            'login' => 'bk.dewi',
            'password' => 'password123',
        ])->assertRedirect(route('counselor.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_guest_is_redirected_to_login_from_counselor_pages(): void
    {
        $this->get(route('counselor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('counselor.exit-permits.index'))->assertRedirect(route('login'));
        $this->get(route('counselor.discipline.index'))->assertRedirect(route('login'));
    }

    public function test_non_counselor_role_cannot_access_counselor_pages(): void
    {
        $teacher = User::where('username', 'guru.agus')->firstOrFail();

        $this->actingAs($teacher)->get(route('counselor.dashboard'))->assertForbidden();
        $this->actingAs($teacher)->get(route('counselor.students.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('counselor.disciplinary-letters.index'))->assertForbidden();
    }

    public function test_counselor_can_render_every_counselor_page(): void
    {
        $permit = $this->pendingPermit();
        $letter = DisciplinaryLetter::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->activeAcademicYearId(),
            'type' => DisciplinaryLetterType::Sp1,
            'reason' => 'Terlambat masuk sekolah selama tiga kali berturut-turut.',
            'issued_at' => now(),
            'issued_by' => $this->counselor->id,
            'status' => 'ACTIVE',
        ]);

        ExitPermitAppeal::create([
            'exit_permit_id' => $permit->id,
            'submitted_at' => now(),
            'reason' => 'Jalan menuju klinik tersendus macet total tanpa jalur alternatif.',
        ]);

        $pages = [
            route('counselor.dashboard') => 'Dashboard',
            route('counselor.exit-permits.index') => 'Izin',
            route('counselor.exit-permits.show', $permit) => $permit->reason_detail,
            route('counselor.appeals.index') => 'Banding',
            route('counselor.discipline.index') => 'Disiplin',
            route('counselor.discipline.create') => 'Disiplin',
            route('counselor.disciplinary-letters.index') => 'Surat',
            route('counselor.disciplinary-letters.show', $letter) => $letter->reason,
            route('counselor.counseling.index') => 'Konseling',
            route('counselor.students.index') => $this->student->full_name,
            route('counselor.students.show', $this->student) => $this->student->full_name,
        ];

        foreach ($pages as $url => $expectedText) {
            $this->actingAs($this->counselor)
                ->get($url)
                ->assertOk()
                ->assertSee($expectedText, escape: false);
        }
    }

    public function test_counselor_approves_pending_exit_permit_and_starts_return_timer(): void
    {
        $permit = $this->pendingPermit();

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.index'))
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approval_note' => 'Keperluan keluarga prioritas dan wali di telepon.',
            ])
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('success');

        $permit->refresh();

        $this->assertSame(ExitPermitStatus::Approved, $permit->status);
        $this->assertSame($this->counselor->staffProfile->id, $permit->approved_by);
        $this->assertNotNull($permit->actual_exit_at);
        $this->assertSame('Keperluan keluarga prioritas dan wali di telepon.', $permit->approval_note);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->counselor->id,
            'action' => 'EXIT_PERMIT_APPROVED',
            'auditable_id' => $permit->id,
        ]);
    }

    public function test_counselor_cannot_approve_an_exit_permit_twice(): void
    {
        $permit = $this->pendingPermit(ExitPermitStatus::Approved);

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.index'))
            ->post(route('counselor.exit-permits.approve', $permit))
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'EXIT_PERMIT_APPROVED',
            'auditable_id' => $permit->id,
        ]);
    }

    public function test_rejecting_an_exit_permit_requires_a_reason(): void
    {
        $permit = $this->pendingPermit();

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.index'))
            ->post(route('counselor.exit-permits.reject', $permit), [])
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHasErrors('rejection_note');

        $this->assertSame(ExitPermitStatus::Pending, $permit->refresh()->status);
    }

    public function test_return_after_deadline_marks_permit_as_late(): void
    {
        $permit = $this->pendingPermit(ExitPermitStatus::Approved, [
            'approved_at' => now()->subHour(),
            'approved_by' => $this->counselor->staffProfile->id,
            'actual_exit_at' => now()->subHour(),
            'planned_return_at' => now()->subMinutes(20),
        ]);

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.index'))
            ->post(route('counselor.exit-permits.complete', $permit))
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('success');

        $permit->refresh();

        $this->assertSame(ExitPermitStatus::Late, $permit->status);
        $this->assertNotNull($permit->actual_return_at);
    }

    public function test_return_before_deadline_marks_permit_as_completed(): void
    {
        $permit = $this->pendingPermit(ExitPermitStatus::Approved, [
            'approved_at' => now()->subMinutes(30),
            'approved_by' => $this->counselor->staffProfile->id,
            'actual_exit_at' => now()->subMinutes(30),
            'planned_return_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.complete', $permit))
            ->assertRedirect(route('counselor.exit-permits.index'));

        $this->assertSame(ExitPermitStatus::Completed, $permit->refresh()->status);
    }

    public function test_return_cannot_be_recorded_for_a_pending_permit(): void
    {
        $permit = $this->pendingPermit();

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.index'))
            ->post(route('counselor.exit-permits.complete', $permit))
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('error');

        $this->assertNull($permit->refresh()->actual_return_at);
    }

    public function test_violation_record_normalizes_points_delta_to_negative(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.discipline.store'), [
                'student_id' => $this->student->id,
                'category_id' => $category->id,
                'points_delta' => 25,
                'occurred_at' => now()->toDateString(),
                'description' => 'Terlambat masuk kelas selama 25 menit.',
                'source_type' => 'MANUAL',
            ])
            ->assertRedirect(route('counselor.discipline.index', ['student_id' => $this->student->id]));

        $record = DisciplineRecord::where('description', 'Terlambat masuk kelas selama 25 menit.')->firstOrFail();

        $this->assertSame(-25, $record->points_delta);
        $this->assertSame($this->counselor->id, $record->created_by);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DISCIPLINE_RECORD_CREATED',
            'auditable_id' => $record->id,
        ]);
    }

    public function test_reward_record_normalizes_points_delta_to_positive(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Reward->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.discipline.store'), [
                'student_id' => $this->student->id,
                'category_id' => $category->id,
                'points_delta' => -30,
                'occurred_at' => now()->toDateString(),
                'description' => 'Juara dua lomba tingkat provinsi.',
                'source_type' => 'MANUAL',
            ]);

        $record = DisciplineRecord::where('description', 'Juara dua lomba tingkat provinsi.')->firstOrFail();

        $this->assertSame(30, $record->points_delta);
    }

    public function test_discipline_record_rejects_zero_point_delta(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.discipline.store'), [
                'student_id' => $this->student->id,
                'category_id' => $category->id,
                'points_delta' => 0,
                'occurred_at' => now()->toDateString(),
                'description' => 'Catatan tanpa perubahan poin.',
            ])
            ->assertSessionHasErrors('points_delta');

        $this->assertDatabaseMissing('discipline_records', [
            'description' => 'Catatan tanpa perubahan poin.',
        ]);
    }

    public function test_future_occurrence_date_is_rejected(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.discipline.store'), [
                'student_id' => $this->student->id,
                'category_id' => $category->id,
                'points_delta' => -5,
                'occurred_at' => now()->addWeek()->toDateString(),
                'description' => 'Catatan untuk tanggal yang belum terjadi.',
            ])
            ->assertSessionHasErrors('occurred_at');
    }

    public function test_deleting_a_discipline_record_restores_the_point_balance(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();
        $academicYearId = $this->activeAcademicYearId();

        $record = DisciplineRecord::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYearId,
            'category_id' => $category->id,
            'points_delta' => -40,
            'occurred_at' => now(),
            'description' => 'Meninggalkan kelas tanpa izin.',
            'source_type' => 'MANUAL',
            'created_by' => $this->counselor->id,
        ]);

        $this->assertSame(60, $this->pointBalance($academicYearId));

        $this->actingAs($this->counselor)
            ->delete(route('counselor.discipline.destroy', $record))
            ->assertRedirect(route('counselor.discipline.index', ['student_id' => $this->student->id]));

        $this->assertDatabaseMissing('discipline_records', ['id' => $record->id]);
        $this->assertSame(100, $this->pointBalance($academicYearId));
    }

    public function test_accepted_appeal_records_no_sanction(): void
    {
        $appeal = $this->pendingAppeal();

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Accepted->value,
            ])
            ->assertRedirect(route('counselor.appeals.index'))
            ->assertSessionHas('success');

        $this->assertSame(AppealDecision::Accepted, $appeal->refresh()->decision);
        $this->assertSame($this->counselor->staffProfile->id, $appeal->decided_by);
        $this->assertDatabaseMissing('discipline_records', ['source_id' => $appeal->id]);
    }

    public function test_rejected_appeal_records_sanction_with_negative_points(): void
    {
        $appeal = $this->pendingAppeal();
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
                'decision_note' => 'Bukti foto dan kronologi tidak mendukung alasan siswa.',
                'record_sanction' => '1',
                'sanction_category_id' => $category->id,
                'sanction_points' => 15,
            ])
            ->assertRedirect(route('counselor.appeals.index'));

        $this->assertSame(AppealDecision::Rejected, $appeal->refresh()->decision);

        $sanction = DisciplineRecord::where('source_id', $appeal->id)->firstOrFail();

        $this->assertSame('APPEAL', $sanction->source_type);
        $this->assertSame(-15, $sanction->points_delta);
        $this->assertSame($category->id, $sanction->category_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'APPEAL_SANCTION_RECORDED',
            'auditable_id' => $appeal->id,
        ]);
    }

    public function test_rejected_appeal_falls_back_to_category_default_points(): void
    {
        $appeal = $this->pendingAppeal();
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();
        $category->update(['default_points' => -12]);

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
                'decision_note' => 'Alasan banding tidak disertai bukti pendukung.',
                'record_sanction' => '1',
                'sanction_category_id' => $category->id,
            ]);

        $this->assertSame(-12, DisciplineRecord::where('source_id', $appeal->id)->firstOrFail()->points_delta);
    }

    public function test_sanction_cannot_be_recorded_without_a_category(): void
    {
        $appeal = $this->pendingAppeal();

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
                'decision_note' => 'Alasan banding tidak disertai bukti pendukung.',
                'record_sanction' => '1',
            ])
            ->assertSessionHasErrors('sanction_category_id');

        $this->assertSame(AppealDecision::Pending, $appeal->refresh()->decision);
    }

    public function test_rejected_appeal_requires_a_decision_note(): void
    {
        $appeal = $this->pendingAppeal();

        $this->actingAs($this->counselor)
            ->post(route('counselor.appeals.decide', $appeal), [
                'decision' => AppealDecision::Rejected->value,
            ])
            ->assertSessionHasErrors('decision_note');
    }

    public function test_counseling_log_is_stored_without_changing_point_balance(): void
    {
        $category = DisciplineCategory::where('type', DisciplineCategoryType::Violation->value)->firstOrFail();
        $academicYearId = $this->activeAcademicYearId();
        $balanceBefore = $this->pointBalance($academicYearId);

        $this->actingAs($this->counselor)
            ->post(route('counselor.counseling.store'), [
                'student_id' => $this->student->id,
                'service_type' => 'COUNSELING',
                'category_id' => $category->id,
                'occurred_at' => now()->toDateString(),
                'summary' => 'Siswa setuju jadwal kunjungan rumah bersama wali '.random_int(1000, 9999).'.',
            ])
            ->assertRedirect(route('counselor.counseling.index', ['student_id' => $this->student->id]));

        $log = DisciplineRecord::where('source_type', 'COUNSELING')->latest('id')->firstOrFail();

        $this->assertSame(0, $log->points_delta);
        $this->assertSame($balanceBefore, $this->pointBalance($academicYearId));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'COUNSELING_LOG_CREATED',
            'auditable_id' => $log->id,
        ]);
    }

    public function test_counselor_issues_a_disciplinary_letter_with_document(): void
    {
        Storage::fake('public');
        $academicYearId = $this->activeAcademicYearId();

        $this->actingAs($this->counselor)
            ->post(route('counselor.disciplinary-letters.store'), [
                'student_id' => $this->student->id,
                'type' => DisciplinaryLetterType::Sp2->value,
                'reason' => 'Pelanggaran berat yang berulang setelah surat peringatan pertama.',
                'issued_at' => now()->toDateString(),
                'notes' => 'Pendampingan orang tua terjadwal setiap hari Senin.',
                'document' => UploadedFile::fake()->create('surat.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('counselor.disciplinary-letters.index', ['student_id' => $this->student->id]));

        $letter = DisciplinaryLetter::where('type', DisciplinaryLetterType::Sp2)->firstOrFail();

        $this->assertSame('ACTIVE', $letter->status);
        $this->assertSame($this->counselor->id, $letter->issued_by);
        $this->assertSame($academicYearId, $letter->academic_year_id);
        Storage::disk('public')->assertExists($letter->document_path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DISCIPLINARY_LETTER_ISSUED',
            'auditable_id' => $letter->id,
        ]);
    }

    public function test_disciplinary_letter_rejects_unsupported_document_type(): void
    {
        Storage::fake('public');

        $this->actingAs($this->counselor)
            ->post(route('counselor.disciplinary-letters.store'), [
                'student_id' => $this->student->id,
                'type' => DisciplinaryLetterType::Sp1->value,
                'reason' => 'Dokumen pendukung yang tidak diizinkan oleh aturan.',
                'issued_at' => now()->toDateString(),
                'document' => UploadedFile::fake()->create('surat.exe', 10, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('document');

        $this->assertDatabaseMissing('disciplinary_letters', ['student_id' => $this->student->id]);
    }

    public function test_revoking_a_disciplinary_letter_deletes_it_and_its_document(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('surat.pdf', 120, 'application/pdf')->store('disciplinary-letters', 'public');

        $letter = DisciplinaryLetter::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->activeAcademicYearId(),
            'type' => DisciplinaryLetterType::Sp1,
            'reason' => 'Surat peringatan yang kemudian dibatalkan oleh sekolah.',
            'issued_at' => now(),
            'issued_by' => $this->counselor->id,
            'document_path' => $path,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($this->counselor)
            ->delete(route('counselor.disciplinary-letters.destroy', $letter))
            ->assertRedirect(route('counselor.disciplinary-letters.index', ['student_id' => $this->student->id]));

        $this->assertDatabaseMissing('disciplinary_letters', ['id' => $letter->id]);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DISCIPLINARY_LETTER_REVOKED',
            'auditable_id' => $letter->id,
        ]);
    }

    public function test_student_list_can_be_filtered_by_nis(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.students.index', ['q' => $this->student->nis]))
            ->assertOk()
            ->assertSee($this->student->full_name);
    }

    protected function pendingPermit(
        ExitPermitStatus $status = ExitPermitStatus::Pending,
        array $overrides = [],
    ): ExitPermit {
        return ExitPermit::create(array_merge([
            'student_id' => $this->student->id,
            'reason_id' => ExitPermitReason::firstOrFail()->id,
            'reason_detail' => 'Izin ke rumah sakit untuk pemeriksaan '.random_int(1000, 9999).'.',
            'requested_at' => now()->subMinutes(30),
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHours(2),
            'status' => $status,
        ], $overrides));
    }

    protected function pendingAppeal(): ExitPermitAppeal
    {
        return ExitPermitAppeal::create([
            'exit_permit_id' => $this->pendingPermit(ExitPermitStatus::Late, [
                'approved_at' => now()->subHours(2),
                'actual_exit_at' => now()->subHours(2),
                'planned_return_at' => now()->subHours(1),
                'actual_return_at' => now()->subMinutes(30),
            ])->id,
            'submitted_at' => now()->subDay(),
            'reason' => 'Siswa sempat terjebak macet parah di jalan raya '.random_int(1000, 9999).'.',
        ]);
    }

    protected function activeAcademicYearId(): int
    {
        return (int) AcademicYear::where('is_active', true)->value('id');
    }

    protected function pointBalance(int $academicYearId): int
    {
        $setting = DisciplineSetting::where('academic_year_id', $academicYearId)->firstOrFail();

        return (int) max(
            $setting->minimum_points,
            $setting->initial_points + DisciplineRecord::where('student_id', $this->student->id)
                ->where('academic_year_id', $academicYearId)
                ->sum('points_delta'),
        );
    }
}
