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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            // Basic identification
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            
            // Type and status
            $table->enum('type', ['cod', 'bank_transfer', 'stripe', 'paypal', 'other'])->default('cod');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Ordering
            $table->integer('sort_order')->default(0);
            
            // Configuration
            $table->text('instructions')->nullable();
            $table->json('configuration')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('code');
            $table->index('type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
