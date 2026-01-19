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
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->string('payment_status_from')->nullable();
            $table->string('payment_status_to')->nullable();
            
            $table->text('comment')->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->boolean('visible_to_customer')->default(false);
            
            $table->timestamp('created_at');
            
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
