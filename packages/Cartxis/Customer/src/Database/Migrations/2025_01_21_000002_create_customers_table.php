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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Password for customer portal (optional)
            $table->string('password')->nullable();
            
            // Customer Group
            $table->foreignId('customer_group_id')
                ->constrained('customer_groups')
                ->onDelete('restrict');
            
            // Company Information (optional for B2B)
            $table->string('company_name', 200)->nullable();
            $table->string('tax_id', 50)->nullable();
            
            // Status and Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('newsletter_subscribed')->default(false);
            
            // Statistics
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0.00);
            $table->decimal('average_order_value', 10, 2)->default(0.00);
            $table->timestamp('last_order_date')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('customer_group_id');
            $table->index('is_active');
            $table->index('created_at');
            $table->index(['first_name', 'last_name']);
        });
        
        // Add fulltext index only for MySQL (not supported in SQLite used for testing)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('customers', function (Blueprint $table) {
                $table->fullText(['first_name', 'last_name', 'email']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
