<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebook_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_id')->constrained('gradebooks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('class_enrollment_id')->nullable()->constrained('class_enrollments')->nullOnDelete();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['gradebook_id', 'student_id'], 'unique_gradebook_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_students');
    }
};
