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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->onDelete('cascade');
            
            // Address Type
            $table->enum('type', ['shipping', 'billing'])->default('shipping');
            $table->string('label', 50)->nullable()->comment('Address label like home, office, etc.');
            
            // Address Fields
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 200)->nullable();
            $table->string('address_line_1', 255);
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->string('country', 2); // ISO 3166-1 alpha-2
            $table->string('phone', 20)->nullable();
            
            // Default Flags
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index(['customer_id', 'is_default_shipping']);
            $table->index(['customer_id', 'is_default_billing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
