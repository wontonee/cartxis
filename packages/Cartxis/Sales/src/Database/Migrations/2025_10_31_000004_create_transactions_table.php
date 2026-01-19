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
            $table->string('type', 50); // payment, refund, authorization, capture
            $table->string('payment_method', 100)->nullable();
            $table->string('gateway', 100)->nullable();
            $table->string('gateway_transaction_id', 191)->nullable();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('status', 50)->default('pending');
            $table->json('response_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('invoice_id');
            $table->index('credit_memo_id');
            $table->index('type');
            $table->index('status');
            $table->index('gateway');
            $table->index('processed_at');
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
