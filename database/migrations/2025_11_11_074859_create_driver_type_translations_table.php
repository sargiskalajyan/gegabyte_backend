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
        Schema::create('driver_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_type_id')->constrained('driver_types')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->unique(['driver_type_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_type_translations');
    }
};
