<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->unique()->constrained('academic_years')->cascadeOnDelete();
            $table->integer('initial_points')->default(100);
            $table->integer('minimum_points')->default(0);
            $table->integer('warning_threshold')->nullable()->default(75);
            $table->integer('sp1_threshold')->nullable()->default(50);
            $table->integer('sp2_threshold')->nullable()->default(30);
            $table->integer('sp3_threshold')->nullable()->default(10);
            $table->timestamps();
        });

        Schema::create('discipline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('type', ['VIOLATION', 'REWARD']);
            $table->integer('default_points');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('discipline_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('discipline_categories')->restrictOnDelete();
            $table->integer('points_delta');
            $table->timestamp('occurred_at')->useCurrent();
            $table->text('description');
            $table->string('source_type', 100)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('disciplinary_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->enum('type', ['SP1', 'SP2', 'SP3']);
            $table->text('reason');
            $table->date('issued_at');
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->string('document_path', 255)->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_letters');
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('discipline_categories');
        Schema::dropIfExists('discipline_settings');
    }
};
