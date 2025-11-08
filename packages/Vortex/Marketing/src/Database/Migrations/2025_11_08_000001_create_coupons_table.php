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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Discount Configuration
            $table->enum('type', [
                'percentage',
                'fixed_amount',
                'free_shipping',
                'buy_x_get_y',
                'fixed_price'
            ]);
            $table->decimal('value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable(); // For percentage type
            $table->decimal('min_order_amount', 10, 2)->nullable();
            
            // Status & Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false); // Show in available coupons list
            $table->boolean('auto_apply')->default(false);
            
            // Stacking & Rules
            $table->boolean('stackable')->default(true);
            $table->boolean('exclude_sale_items')->default(false);
            $table->integer('priority')->default(0);
            
            // Usage Limits
            $table->integer('usage_limit_total')->nullable(); // null = unlimited
            $table->integer('usage_limit_per_customer')->nullable();
            $table->integer('usage_count')->default(0);
            
            // Schedule
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            
            // Time Restrictions (JSON)
            $table->json('days_of_week')->nullable(); // ["monday", "friday"] or null
            $table->json('time_restrictions')->nullable(); // {"start": "09:00", "end": "17:00"}
            
            // Customer Restrictions (JSON)
            $table->json('customer_groups')->nullable(); // [1,2,3] group IDs
            $table->boolean('first_order_only')->default(false);
            $table->integer('min_account_age_days')->nullable();
            
            // Product/Category Restrictions (JSON)
            $table->json('applicable_products')->nullable(); // Product IDs
            $table->json('applicable_categories')->nullable(); // Category IDs
            $table->json('excluded_products')->nullable();
            $table->json('excluded_categories')->nullable();
            
            // Buy X Get Y specific fields
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();
            $table->json('buy_products')->nullable(); // Product IDs to buy
            $table->json('get_products')->nullable(); // Product IDs to get free
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['is_active', 'start_date', 'end_date'], 'active_coupons_idx');
            $table->index('auto_apply');
            $table->index('is_public');
            $table->index(['created_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
