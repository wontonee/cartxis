<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'phonepe' type to the payment_methods enum column.
     */
    public function up(): void
    {
        // Add 'phonepe' to the enum type
        // MySQL requires modifying the column to update enum values
        DB::statement("ALTER TABLE payment_methods MODIFY COLUMN type ENUM('cod', 'bank_transfer', 'stripe', 'paypal', 'razorpay', 'phonepe', 'payumoney', 'other') DEFAULT 'cod'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove phonepe from enum (keep other values)
        DB::statement("ALTER TABLE payment_methods MODIFY COLUMN type ENUM('cod', 'bank_transfer', 'stripe', 'paypal', 'razorpay', 'payumoney', 'other') DEFAULT 'cod'");
    }
};
