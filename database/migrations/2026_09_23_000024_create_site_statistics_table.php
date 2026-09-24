<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50)->default('HERO');
            $table->string('key', 50)->unique();
            $table->string('label', 100);
            $table->string('value', 100);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_statistics');
    }
};
