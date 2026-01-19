<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_zone_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_zone_id')->constrained('tax_zones')->onDelete('cascade');
            $table->string('country_code', 2);
            $table->string('state_code', 100)->nullable();
            $table->string('postal_code_pattern')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('state_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_zone_locations');
    }
};
