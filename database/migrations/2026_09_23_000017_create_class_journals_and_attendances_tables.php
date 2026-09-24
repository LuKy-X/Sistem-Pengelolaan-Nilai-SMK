<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->restrictOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('teaching_schedules')->nullOnDelete();
            $table->date('journal_date');
            $table->foreignId('start_period_id')->constrained('lesson_periods')->restrictOnDelete();
            $table->foreignId('end_period_id')->constrained('lesson_periods')->restrictOnDelete();
            $table->text('material');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('teacher_profiles')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('journal_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('class_journals')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->enum('status', ['PRESENT', 'SICK', 'PERMIT', 'ABSENT'])->default('PRESENT');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['journal_id', 'student_id'], 'unique_journal_student_attendance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_attendances');
        Schema::dropIfExists('class_journals');
    }
};
