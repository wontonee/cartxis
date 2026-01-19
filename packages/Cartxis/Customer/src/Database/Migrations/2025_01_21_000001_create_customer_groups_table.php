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
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#3B82F6');
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            
            // Auto-assignment rules
            $table->json('auto_assignment_rules')->nullable()->comment('{"min_orders": 10, "min_amount": 10000}');
            
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('status');
            $table->index('is_default');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};
