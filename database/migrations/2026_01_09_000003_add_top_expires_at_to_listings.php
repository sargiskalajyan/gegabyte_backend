<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'top_expires_at')) {
                $table->timestamp('top_expires_at')->nullable()->after('is_top');
                $table->index('top_expires_at', 'listing_top_expires_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'top_expires_at')) {
                $table->dropIndex('listing_top_expires_idx');
                $table->dropColumn('top_expires_at');
            }
        });
    }
};
