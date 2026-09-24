<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_category_id')->constrained('achievement_categories')->restrictOnDelete();
            $table->string('title', 200);
            $table->string('scope', 50);
            $table->string('level', 50);
            $table->date('achievement_date');
            $table->string('organizer', 150)->nullable();
            $table->string('rank', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('achievement_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('role', 100)->nullable();
            $table->text('description')->nullable();

            $table->unique(['achievement_id', 'student_id'], 'unique_achievement_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_participants');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('achievement_categories');
    }
};
