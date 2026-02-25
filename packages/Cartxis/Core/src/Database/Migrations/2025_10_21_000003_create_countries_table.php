<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 2)->unique()->comment('ISO 3166-1 alpha-2');
            $table->string('code3', 3)->nullable()->comment('ISO 3166-1 alpha-3');
            $table->string('phone_code', 10)->nullable();
            $table->string('currency_code', 3)->nullable()->comment('ISO 4217');
            $table->string('currency_symbol', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
