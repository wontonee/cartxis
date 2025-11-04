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
        if (!Schema::hasTable('shipping_rates')) {
            Schema::create('shipping_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipping_method_id')
                      ->constrained('shipping_methods')
                      ->cascadeOnDelete();
                $table->string('country', 2); // ISO country code
                $table->string('state', 2)->nullable(); // ISO state code or null for entire country
                $table->decimal('min_weight', 8, 3)->default(0);
                $table->decimal('max_weight', 8, 3);
                $table->decimal('base_cost', 10, 2)->default(0);
                $table->decimal('cost_per_kg', 10, 4)->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                // Indexes for query optimization
                $table->index('shipping_method_id');
                $table->index('country');
                $table->index('state');
                $table->index(['country', 'state']);
                $table->index('status');
                $table->unique(['shipping_method_id', 'country', 'state', 'min_weight', 'max_weight'], 'shipping_rates_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
