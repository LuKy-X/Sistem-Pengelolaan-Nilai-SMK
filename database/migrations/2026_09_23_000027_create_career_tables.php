<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('career_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('industry', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('logo', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('career_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('career_companies')->cascadeOnDelete();
            $table->enum('type', ['JOB', 'INTERNSHIP'])->default('JOB');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('location', 100)->nullable();
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->string('application_link', 255)->nullable();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamps();
        });

        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('career_opportunities')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->restrictOnDelete();
            $table->timestamp('applied_at')->useCurrent();
            $table->string('status', 30)->default('SUBMITTED');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['opportunity_id', 'student_id'], 'unique_career_application');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('career_opportunities');
        Schema::dropIfExists('career_companies');
        Schema::dropIfExists('career_services');
    }
};
