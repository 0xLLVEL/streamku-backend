<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_shows', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tmdb_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('overview')->nullable();
            $table->string('tagline')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date')->nullable();
            $table->date('last_air_date')->nullable();
            $table->unsignedSmallInteger('number_of_seasons')->default(0);
            $table->unsignedSmallInteger('number_of_episodes')->default(0);
            $table->unsignedSmallInteger('episode_run_time')->nullable();
            $table->decimal('vote_average', 3, 1)->default(0);
            $table->unsignedInteger('vote_count')->default(0);
            $table->decimal('popularity', 10, 3)->default(0);
            $table->string('original_language', 10)->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('genre_tv_show', function (Blueprint $table) {
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tv_show_id')->constrained()->cascadeOnDelete();
            $table->primary(['genre_id', 'tv_show_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genre_tv_show');
        Schema::dropIfExists('tv_shows');
    }
};
