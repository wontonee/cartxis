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
        if (!Schema::hasTable('customer_addresses')) {
            return;
        }

        if (Schema::hasColumn('customer_addresses', 'label')) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('label', 50)->nullable()->after('type')->comment('Address label like home, office, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('customer_addresses')) {
            return;
        }

        if (!Schema::hasColumn('customer_addresses', 'label')) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
