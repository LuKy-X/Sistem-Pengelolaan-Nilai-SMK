<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_column_id')->nullable()->constrained('gradebook_columns')->nullOnDelete();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->restrictOnDelete();
            $table->enum('type', ['TASK', 'QUIZ', 'PROJECT', 'EXAM', 'OTHER'])->default('TASK');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->boolean('submission_required')->default(true);
            $table->foreignId('rubric_id')->nullable()->constrained('rubrics')->nullOnDelete();
            $table->foreignId('created_by')->constrained('teacher_profiles')->restrictOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->timestamps();
        });

        Schema::create('assessment_late_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->unique()->constrained('assessments')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->enum('reduction_type', ['PERCENTAGE', 'FIXED_POINTS'])->default('PERCENTAGE');
            $table->decimal('reduction_value', 5, 2)->default(5.00);
            $table->integer('interval')->default(60);
            $table->integer('grace_period_minutes')->default(15);
            $table->decimal('minimum_max_score', 5, 2)->default(50.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_late_policies');
        Schema::dropIfExists('assessments');
    }
};
