<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exit_permit_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exit_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('reason_id')->constrained('exit_permit_reasons')->restrictOnDelete();
            $table->text('reason_detail');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('planned_exit_at');
            $table->timestamp('planned_return_at');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->timestamp('actual_exit_at')->nullable();
            $table->timestamp('actual_return_at')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED', 'LATE', 'CANCELLED'])->default('PENDING');
            $table->text('approval_note')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });

        Schema::create('exit_permit_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_permit_id')->unique()->constrained('exit_permits')->cascadeOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
            $table->text('reason');
            $table->enum('decision', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->text('decision_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exit_permit_appeals');
        Schema::dropIfExists('exit_permits');
        Schema::dropIfExists('exit_permit_reasons');
    }
};
