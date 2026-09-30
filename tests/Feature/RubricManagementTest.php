<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\GradebookColumn;
use App\Models\Role;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RubricManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;

    protected TeacherProfile $teacherProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        $this->teacherUser = User::factory()->create();
        $this->teacherUser->roles()->attach($role);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacherUser->id]);
    }

    public function test_teacher_can_view_rubric_detail(): void
    {
        $rubric = Rubric::create([
            'name' => 'Rubrik Presentasi Web',
            'description' => 'Pedoman penskoran presentasi',
            'created_by' => $this->teacherProfile->id,
            'status' => 'PUBLISHED',
        ]);

        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Penguasaan Materi',
            'description' => 'Mampu menjawab pertanyaan penguji',
            'max_points' => 50,
            'sort_order' => 1,
        ]);

        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Kerapihan Slide',
            'description' => 'Slide terstruktur dan estetik',
            'max_points' => 50,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($this->teacherUser)->get(route('teacher.rubrics.show', $rubric));

        $response->assertStatus(200);
        $response->assertSee('Rubrik Presentasi Web');
        $response->assertSee('Total Skor Maksimum');
        $response->assertSee('100 Poin');
        $response->assertSee('Edit Rubrik Ini');
        $response->assertSee('!text-white', false);
    }

    public function test_teacher_can_edit_and_update_unused_rubric(): void
    {
        $rubric = Rubric::create([
            'name' => 'Rubrik Desain UI',
            'description' => 'Deskripsi lama',
            'created_by' => $this->teacherProfile->id,
            'status' => 'PUBLISHED',
        ]);

        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Kriteria Lama',
            'description' => 'Deskripsi lama',
            'max_points' => 100,
            'sort_order' => 1,
        ]);

        // Access edit form
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.rubrics.edit', $rubric));
        $response->assertStatus(200);
        $response->assertSee('Edit Rubrik Penilaian');
        $response->assertSee('Rubrik Desain UI');

        // Submit update
        $updateResponse = $this->actingAs($this->teacherUser)->put(route('teacher.rubrics.update', $rubric), [
            'name' => 'Rubrik Desain UI Diperbarui',
            'description' => 'Deskripsi baru',
            'criteria' => [
                [
                    'criterion' => 'Tata Letak & Tipografi',
                    'description' => 'Hierarki visual jelas',
                    'max_points' => 60,
                ],
                [
                    'criterion' => 'Aksesibilitas Warna',
                    'description' => 'Kontras rasio memenuhi WCAG AA',
                    'max_points' => 40,
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('teacher.rubrics.show', $rubric));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('rubrics', [
            'id' => $rubric->id,
            'name' => 'Rubrik Desain UI Diperbarui',
            'description' => 'Deskripsi baru',
        ]);

        $this->assertDatabaseHas('rubric_criteria', [
            'rubric_id' => $rubric->id,
            'criterion' => 'Tata Letak & Tipografi',
            'max_points' => 60,
        ]);

        $this->assertDatabaseMissing('rubric_criteria', [
            'rubric_id' => $rubric->id,
            'criterion' => 'Kriteria Lama',
        ]);
    }

    public function test_teacher_cannot_edit_or_update_used_rubric(): void
    {
        $rubric = Rubric::create([
            'name' => 'Rubrik Praktikum Backend',
            'created_by' => $this->teacherProfile->id,
            'status' => 'PUBLISHED',
        ]);

        RubricCriterion::create([
            'rubric_id' => $rubric->id,
            'criterion' => 'Endpoint API',
            'max_points' => 100,
            'sort_order' => 1,
        ]);

        // Attach rubric to an assessment
        $schoolClass = SchoolClass::factory()->create();
        $assignment = TeachingAssignment::factory()->create([
            'teacher_id' => $this->teacherProfile->id,
            'class_id' => $schoolClass->id,
        ]);
        $column = GradebookColumn::factory()->create();

        Assessment::factory()->create([
            'teaching_assignment_id' => $assignment->id,
            'gradebook_column_id' => $column->id,
            'rubric_id' => $rubric->id,
        ]);

        $this->assertTrue($rubric->assessments()->exists());

        // Attempting to visit edit page must redirect back with error
        $editResponse = $this->actingAs($this->teacherUser)->get(route('teacher.rubrics.edit', $rubric));
        $editResponse->assertRedirect(route('teacher.rubrics.show', $rubric));
        $editResponse->assertSessionHas('error');

        // Attempting to submit update must also redirect back with error
        $updateResponse = $this->actingAs($this->teacherUser)->put(route('teacher.rubrics.update', $rubric), [
            'name' => 'Nama Yang Harusnya Ditolak',
            'criteria' => [
                [
                    'criterion' => 'Kriteria Baru',
                    'max_points' => 100,
                ],
            ],
        ]);
        $updateResponse->assertRedirect(route('teacher.rubrics.show', $rubric));
        $updateResponse->assertSessionHas('error');

        // Confirm rubric in database was NOT changed
        $this->assertDatabaseHas('rubrics', [
            'id' => $rubric->id,
            'name' => 'Rubrik Praktikum Backend',
        ]);

        // Check show page shows lock badge instead of edit button
        $showResponse = $this->actingAs($this->teacherUser)->get(route('teacher.rubrics.show', $rubric));
        $showResponse->assertSee('Terkunci (Sudah Digunakan)');
        $showResponse->assertDontSee('Edit Rubrik Ini');
    }
}
