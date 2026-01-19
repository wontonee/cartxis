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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            
            // Promotion Type
            $table->enum('type', [
                'catalog_rule',
                'cart_rule',
                'bundle',
                'flash_sale',
                'tiered_pricing'
            ]);
            
            // Discount Configuration
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            
            // Status & Priority
            $table->boolean('is_active')->default(true);
            $table->boolean('stop_rules_processing')->default(false); // Stop applying other promotions
            $table->integer('priority')->default(0); // Higher = applied first
            
            // Stacking Rules
            $table->boolean('stackable')->default(true);
            $table->boolean('stackable_with_coupons')->default(true);
            
            // Display Settings
            $table->boolean('show_badge')->default(false);
            $table->string('badge_text', 50)->nullable();
            $table->string('badge_color', 7)->default('#ffffff'); // Text color
            $table->string('badge_bg_color', 7)->default('#ff0000'); // Background
            $table->enum('badge_position', [
                'top-left',
                'top-right',
                'bottom-left',
                'bottom-right'
            ])->default('top-right');
            $table->boolean('show_countdown')->default(false); // For flash sales
            
            // Schedule
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            
            // Usage Tracking
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('usage_per_customer')->nullable();
            
            // Conditions (JSON)
            // Example: {"min_order_amount": 100, "min_items": 3, "customer_groups": [1,2]}
            $table->json('conditions')->nullable();
            
            // Actions (JSON)
            // Example: {"applicable_categories": [1,2], "excluded_products": [5,10]}
            $table->json('actions')->nullable();
            
            // Bundle Products (JSON) - for type='bundle'
            // Example: [{"product_id": 1, "quantity": 2}, {"product_id": 5, "quantity": 1}]
            $table->json('bundle_products')->nullable();
            
            // Tiered Pricing (JSON) - for type='tiered_pricing'
            // Example: [{"min_qty": 5, "max_qty": 9, "discount_pct": 10}, ...]
            $table->json('price_tiers')->nullable();
            
            // Analytics
            $table->decimal('total_revenue_generated', 12, 2)->default(0);
            $table->integer('conversion_count')->default(0);
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_active', 'start_date', 'end_date'], 'active_promotions_idx');
            $table->index(['type', 'priority'], 'promotion_type_priority_idx');
            $table->index('show_badge');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
