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
        Schema::create('transmission_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transmission_id')->constrained('transmissions')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->unique(['transmission_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmission_translations');
    }
};
