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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Unique identifier: admin.dashboard');
            $table->string('title')->comment('Display name');
            $table->string('icon')->nullable()->comment('Icon name: shopping-cart, users, etc.');
            $table->string('route')->nullable()->comment('Laravel route name');
            $table->string('url')->nullable()->comment('External URL if no route');
            $table->foreignId('parent_id')->nullable()
                ->constrained('menu_items')
                ->onDelete('cascade')
                ->comment('Parent menu item ID');
            $table->integer('order')->default(0)->comment('Sorting order');
            $table->string('permission')->nullable()->comment('Required permission');
            $table->enum('location', ['admin', 'shop'])->default('admin')
                ->comment('Where to display: admin or shop');
            $table->boolean('active')->default(true)->comment('Enable/disable menu item');
            $table->json('meta')->nullable()->comment('Extra data: badge, tooltip, etc.');
            $table->string('extension_code')->nullable()->comment('Extension that owns this menu');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['location', 'active', 'order']);
            $table->index('parent_id');
            $table->index('extension_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
