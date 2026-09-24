<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->default('DRAFT');
            $table->longText('content')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('teacher_profiles')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id'], 'unique_student_submission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');
    }
};
