<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('title', 150);
            $table->date('registration_start');
            $table->date('registration_end');
            $table->text('description')->nullable();
            $table->enum('status', ['DRAFT', 'OPEN', 'CLOSED'])->default('DRAFT');
            $table->timestamps();
        });

        Schema::create('admission_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('step_number')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('admission_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admission_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('admission_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->string('name', 150);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_free')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_fee_items');
        Schema::dropIfExists('admission_requirements');
        Schema::dropIfExists('admission_paths');
        Schema::dropIfExists('admission_schedule_items');
        Schema::dropIfExists('admission_periods');
    }
};
