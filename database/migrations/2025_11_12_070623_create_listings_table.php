<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fuel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transmission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('drivetrain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('condition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('make_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('engine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('engine_size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_type_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('gas_equipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wheel_size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('headlight_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interior_color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interior_material_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('steering_wheel_id')->nullable()->constrained()->nullOnDelete();



            // Core Data
            $table->year('year')->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->string('vin', 17)->nullable()->unique();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('exchange')->default(false);
            $table->text('description')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'published', 'rejected', 'expired'])
                ->default('draft');

            $table->timestamp('featured_until')->nullable();
            $table->timestamp('published_until')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->boolean('is_top')->default(false);
            $table->timestamp('top_expires_at')->nullable();


            $table->timestamps();

            // Common filters
            $table->index(['status', 'exchange'], 'listing_status_exchange_idx');
            $table->index(['status', 'category_id'], 'listing_status_category_idx');
            $table->index(['make_id', 'car_model_id'], 'listing_make_model_idx');
            $table->index(['location_id', 'category_id'], 'listing_location_category_idx');
            $table->index('top_expires_at', 'listing_top_expires_idx');

            // Search / sorting
            $table->index('price', 'listing_price_idx');
            $table->index('year', 'listing_year_idx');
            $table->index('mileage', 'listing_mileage_idx');
            $table->index('views', 'listing_views_idx');
            $table->index('featured_until', 'listing_featured_idx');
            $table->index('published_until', 'listing_published_idx');
            $table->index('is_top');

            // Full-text search
            $table->fullText(['description'], 'listing_fulltext_idx');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
