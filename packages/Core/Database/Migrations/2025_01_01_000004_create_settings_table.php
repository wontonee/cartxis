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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key: general.site_name');
            $table->text('value')->nullable()->comment('Setting value');
            $table->string('type')->default('text')->comment('Value type: text, json, boolean, etc.');
            $table->string('group')->default('general')->comment('Settings group');
            $table->boolean('is_public')->default(false)->comment('Can be accessed by frontend');
            $table->string('extension_code')->nullable()->comment('Extension that owns this setting');
            $table->timestamps();
            
            $table->index('group');
            $table->index('is_public');
            $table->index('extension_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
