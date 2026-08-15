<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watch_parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('mediable'); // morphs for movies/tv shows
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_parties');
    }
};
