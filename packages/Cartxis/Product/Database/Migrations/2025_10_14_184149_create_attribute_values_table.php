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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade')->comment('Related attribute');
            $table->string('value')->comment('Attribute value (e.g., Red, Large)');
            $table->string('label')->nullable()->comment('Display label (optional, defaults to value)');
            $table->string('color_code', 7)->nullable()->comment('Hex color code for color attributes');
            $table->string('image')->nullable()->comment('Image path for visual attributes');
            $table->text('description')->nullable()->comment('Value description');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->boolean('is_default')->default(false)->comment('Is this the default value?');
            $table->timestamps();
            
            // Indexes
            $table->index('attribute_id');
            $table->index('sort_order');
            $table->index(['attribute_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
