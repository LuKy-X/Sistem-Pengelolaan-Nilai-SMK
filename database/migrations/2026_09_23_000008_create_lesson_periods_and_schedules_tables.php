<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('period_number')->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('label', 50);
            $table->timestamps();
        });

        Schema::create('teaching_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 7 = Minggu
            $table->foreignId('start_period_id')->constrained('lesson_periods')->restrictOnDelete();
            $table->foreignId('end_period_id')->constrained('lesson_periods')->restrictOnDelete();
            $table->string('room', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_schedules');
        Schema::dropIfExists('lesson_periods');
    }
};
