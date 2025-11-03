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
        Schema::table('customers', function (Blueprint $table) {
            // Drop existing unique constraint on email
            $table->dropUnique(['email']);
            
            // Add composite unique constraint on email + is_guest
            // This allows: john@example.com (registered) AND john@example.com (guest)
            $table->unique(['email', 'is_guest'], 'customers_email_is_guest_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('customers_email_is_guest_unique');
            
            // Restore original unique constraint on email only
            $table->unique('email');
        });
    }
};
