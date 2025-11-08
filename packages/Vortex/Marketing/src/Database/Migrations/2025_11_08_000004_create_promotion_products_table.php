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
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Optional Overrides
            $table->decimal('discount_override', 10, 2)->nullable();
            $table->string('badge_text_override', 50)->nullable();
            
            $table->timestamps();
            
            // Ensure unique promotion-product combination
            $table->unique(['promotion_id', 'product_id'], 'promotion_product_unique');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
    }
};
