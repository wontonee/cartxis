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
        // Attributes definition (Color, Size, Material, etc.)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['select', 'multiselect', 'text', 'textarea', 'date', 'boolean', 'price'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_configurable')->default(false); // Can be used for variants
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_configurable');
        });

        // Attribute options (Red, Blue, Small, Large, etc.)
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->string('swatch_value')->nullable(); // For color hex codes or image paths
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('attribute_id');
        });

        // Product attribute values
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('text_value')->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->date('date_value')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'attribute_id']);
        });

        // Product variants (configurable products)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // Parent configurable product
            $table->foreignId('variant_id')->constrained('products')->cascadeOnDelete(); // Child simple product
            $table->timestamps();
            
            $table->unique(['product_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
};
