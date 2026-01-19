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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            
            // Pricing
            $table->decimal('price', 12, 4)->default(0);
            $table->decimal('cost', 12, 4)->nullable();
            $table->decimal('special_price', 12, 4)->nullable();
            $table->date('special_price_from')->nullable();
            $table->date('special_price_to')->nullable();
            
            // Status & Visibility
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->enum('visibility', ['catalog', 'search', 'both', 'none'])->default('both');
            $table->boolean('featured')->default(false);
            $table->boolean('new')->default(false);
            
            // Inventory
            $table->integer('quantity')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock');
            $table->boolean('manage_stock')->default(true);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->integer('notify_stock_qty')->default(5);
            
            // Product Type & Configuration
            $table->enum('type', ['simple', 'configurable', 'virtual', 'downloadable'])->default('simple');
            $table->string('weight')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Sorting & Display
            $table->integer('sort_order')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('sales_count')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('sku');
            $table->index('slug');
            $table->index('status');
            $table->index('featured');
            $table->index('new');
            $table->index(['status', 'visibility']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
