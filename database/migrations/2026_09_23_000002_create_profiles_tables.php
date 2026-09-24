<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip', 30)->unique();
            $table->string('full_name', 150);
            $table->enum('gender', ['MALE', 'FEMALE'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->unique();
            $table->string('full_name', 150);
            $table->enum('gender', ['MALE', 'FEMALE']);
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_number', 30)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('photo', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('teacher_profiles');
    }
};
