<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_number');
            $table->unsignedInteger('size');
            $table->string('path');
            $table->string('checksum')->nullable();
            $table->timestamps();

            $table->unique(['upload_id', 'chunk_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_chunks');
    }
};
