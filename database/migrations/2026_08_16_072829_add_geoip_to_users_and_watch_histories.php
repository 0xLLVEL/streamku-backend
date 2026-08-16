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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('preferences');
            $table->string('country', 2)->nullable()->after('ip_address');
        });

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('last_watched_at');
            $table->string('country', 2)->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country']);
        });

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country']);
        });
    }
};
