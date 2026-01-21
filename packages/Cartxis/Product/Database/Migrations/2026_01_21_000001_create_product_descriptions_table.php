<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->json('bullet_points')->nullable();
            $table->json('keywords')->nullable();
            $table->string('tone', 50)->nullable();
            $table->string('language', 10)->default('en');
            $table->float('confidence_score')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_descriptions');
    }
};
