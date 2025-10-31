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
        Schema::create('credit_memo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_memo_id')->constrained('credit_memos')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->string('product_name');
            $table->string('sku', 100)->nullable();
            
            // Quantities
            $table->integer('qty_refunded');
            
            // Pricing
            $table->decimal('price', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('row_total', 12, 2);
            
            // Inventory
            $table->boolean('restore_stock')->default(true);
            $table->boolean('stock_restored')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index('credit_memo_id');
            $table->index('order_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_memo_items');
    }
};
