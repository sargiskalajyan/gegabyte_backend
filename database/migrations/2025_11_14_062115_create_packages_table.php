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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->unsignedInteger('price')
                ->default(0)
                ->comment('Price in AMD');

            $table->unsignedSmallInteger('duration_days')
                ->nullable()
                ->comment('Number of days package is valid. Null = no expiration.');

            $table->unsignedSmallInteger('max_active_listings')
                ->default(1)
                ->comment('Maximum active listings allowed under this package');

            $table->unsignedSmallInteger('included_featured_days')
                ->default(0)
                ->comment('How many days user can feature listings for free');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether the package is available to users');

            $table->timestamps();
            $table->softDeletes(); // optional delete tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
