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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO 4217 currency code (USD, EUR, etc.)');
            $table->string('name', 100)->comment('Display name (US Dollar, Euro, etc.)');
            $table->string('symbol', 10)->comment('Currency symbol ($, €, £, etc.)');
            $table->enum('symbol_position', ['before', 'after'])->default('before')->comment('Symbol position relative to amount');
            $table->tinyInteger('decimal_places')->default(2)->comment('Number of decimal places');
            $table->decimal('exchange_rate', 20, 10)->default(1.0000000000)->comment('Exchange rate relative to base currency');
            $table->boolean('is_default')->default(false)->comment('Default currency');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('is_default');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
