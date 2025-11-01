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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('credit_memo_id')->nullable()->constrained('credit_memos')->onDelete('set null');
            $table->string('transaction_number')->unique();
            $table->enum('type', ['payment', 'refund', 'authorization', 'capture'])->default('payment');
            $table->string('payment_method');
            $table->string('gateway'); // stripe, paypal, razorpay, etc.
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('response_data')->nullable(); // Gateway response
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('transaction_number');
            $table->index('order_id');
            $table->index('gateway_transaction_id');
            $table->index('status');
            $table->index('type');
            $table->index('gateway');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
