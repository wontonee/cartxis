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
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Extension code: vortex-payment-stripe');
            $table->string('name')->comment('Display name');
            $table->text('description')->nullable()->comment('Extension description');
            $table->string('version')->comment('Semantic version: 1.0.0');
            $table->string('author')->nullable()->comment('Author name');
            $table->string('author_url')->nullable()->comment('Author website');
            $table->string('icon')->nullable()->comment('Extension icon class');
            $table->json('requires')->nullable()->comment('Required extensions/packages');
            $table->json('config')->nullable()->comment('Extension configuration');
            $table->boolean('active')->default(false)->comment('Is extension active');
            $table->boolean('installed')->default(false)->comment('Is extension installed');
            $table->timestamp('installed_at')->nullable()->comment('Installation timestamp');
            $table->timestamps();
            
            // Indexes
            $table->index(['active', 'installed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extensions');
    }
};
