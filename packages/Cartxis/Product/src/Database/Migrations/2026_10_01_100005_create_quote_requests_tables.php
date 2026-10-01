<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('company')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('message')->nullable();
            $table->string('status', 32)->default('new'); // new, reviewed, quoted, closed
            $table->text('admin_notes')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_sku')->nullable();
            $table->decimal('product_price', 12, 4)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
