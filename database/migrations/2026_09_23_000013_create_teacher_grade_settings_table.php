<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_grade_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->unique()->constrained('teacher_profiles')->cascadeOnDelete();
            $table->boolean('default_late_enabled')->default(true);
            $table->enum('default_reduction_type', ['PERCENTAGE', 'FIXED_POINTS'])->default('PERCENTAGE');
            $table->decimal('default_reduction_value', 5, 2)->default(5.00);
            $table->integer('default_interval')->default(60); // menit
            $table->integer('default_grace_minutes')->default(15); // menit
            $table->decimal('default_min_max_score', 5, 2)->default(50.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_grade_settings');
    }
};
