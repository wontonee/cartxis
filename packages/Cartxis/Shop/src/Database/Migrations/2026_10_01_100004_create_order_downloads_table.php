<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_download_id')->nullable()->constrained('product_downloads')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('token', 64)->unique();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_downloads');
    }
};
