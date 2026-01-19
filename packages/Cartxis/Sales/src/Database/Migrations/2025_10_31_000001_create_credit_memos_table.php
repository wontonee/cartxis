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
        Schema::create('credit_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('credit_memo_number')->unique();
            $table->string('status', 50)->default('pending'); // pending, complete, cancelled
            
            // Amounts
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('shipping_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('adjustment_positive', 12, 2)->default(0.00); // Restocking fee
            $table->decimal('adjustment_negative', 12, 2)->default(0.00); // Goodwill refund
            $table->decimal('grand_total', 12, 2)->default(0.00);
            
            // Refund details
            $table->string('refund_status', 50)->default('pending'); // pending, processed, failed
            $table->string('refund_method', 50)->nullable(); // original_payment, store_credit, manual
            $table->timestamp('refunded_at')->nullable();
            
            // Inventory
            $table->boolean('restore_inventory')->default(true);
            $table->timestamp('inventory_restored_at')->nullable();
            
            // Notes
            $table->text('notes')->nullable(); // Customer-visible notes
            $table->text('admin_notes')->nullable(); // Internal notes
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('invoice_id');
            $table->index('credit_memo_number');
            $table->index('status');
            $table->index('refund_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_memos');
    }
};
