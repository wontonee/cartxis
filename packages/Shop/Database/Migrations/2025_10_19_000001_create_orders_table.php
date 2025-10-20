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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();
            
            // Pricing
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Payment & Shipping
            $table->string('payment_method')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            
            // Customer Info (for guest checkout)
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('order_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
