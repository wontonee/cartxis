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
            // Drop existing foreign key constraint
            $table->dropForeign(['customer_group_id']);
            
            // Make customer_group_id nullable
            $table->foreignId('customer_group_id')->nullable()->change();
            
            // Re-add foreign key constraint with nullable
            $table->foreign('customer_group_id')
                ->references('id')
                ->on('customer_groups')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['customer_group_id']);
            
            // Make customer_group_id NOT nullable (restore original)
            $table->foreignId('customer_group_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('customer_group_id')
                ->references('id')
                ->on('customer_groups')
                ->onDelete('restrict');
        });
    }
};
