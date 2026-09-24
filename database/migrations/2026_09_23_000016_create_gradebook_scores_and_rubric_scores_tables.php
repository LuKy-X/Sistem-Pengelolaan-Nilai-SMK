<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebook_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_column_id')->constrained('gradebook_columns')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->decimal('raw_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->decimal('max_score_snapshot', 5, 2)->nullable()->default(100.00);
            $table->integer('late_minutes')->nullable()->default(0);
            $table->decimal('late_deduction', 5, 2)->nullable()->default(0.00);
            $table->text('feedback')->nullable();
            $table->enum('source', ['MANUAL', 'RUBRIC'])->default('MANUAL');
            $table->foreignId('graded_by')->nullable()->constrained('teacher_profiles')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['gradebook_column_id', 'student_id'], 'unique_gradebook_score');
        });

        Schema::create('rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_score_id')->constrained('gradebook_scores')->cascadeOnDelete();
            $table->foreignId('rubric_criterion_id')->constrained('rubric_criteria')->restrictOnDelete();
            $table->decimal('points_awarded', 5, 2);
            $table->string('note', 255)->nullable();
            $table->foreignId('graded_by')->constrained('teacher_profiles')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['gradebook_score_id', 'rubric_criterion_id'], 'unique_rubric_criterion_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_scores');
        Schema::dropIfExists('gradebook_scores');
    }
};
