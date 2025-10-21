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
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO 639-1 language code (en, es, fr, etc.)');
            $table->string('name', 100)->comment('Display name (English, Spanish, etc.)');
            $table->string('native_name', 100)->nullable()->comment('Native language name (Español, Français, etc.)');
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr')->comment('Text direction');
            $table->boolean('is_default')->default(false)->comment('Default locale');
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
        Schema::dropIfExists('locales');
    }
};
