<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('student_profiles')->restrictOnDelete();
            $table->year('graduation_year');
            $table->string('current_occupation', 150)->nullable();
            $table->string('current_company', 150)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('social_link', 255)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('alumni_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_profile_id')->constrained('alumni_profiles')->cascadeOnDelete();
            $table->string('title', 200);
            $table->longText('story');
            $table->text('career_story')->nullable();
            $table->text('quote')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_stories');
        Schema::dropIfExists('alumni_profiles');
    }
};
