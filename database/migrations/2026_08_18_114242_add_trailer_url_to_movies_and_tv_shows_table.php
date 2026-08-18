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
        Schema::table('movies', function (Blueprint $table) {
            $table->string('trailer_url', 500)->nullable()->after('tagline');
        });

        Schema::table('tv_shows', function (Blueprint $table) {
            $table->string('trailer_url', 500)->nullable()->after('tagline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies_and_tv_shows', function (Blueprint $table) {
            //
        });
    }
};
