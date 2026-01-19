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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->enum('type', ['flat-rate', 'calculated'])->default('flat-rate');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('display_order')->default(0);
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->decimal('cost_per_kg', 10, 4)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes for query optimization
            $table->index('status');
            $table->index('is_default');
            $table->index('type');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
