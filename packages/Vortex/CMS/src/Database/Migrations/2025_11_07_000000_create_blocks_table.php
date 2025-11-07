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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique()->comment('Unique identifier for embedding block');
            $table->string('title');
            $table->enum('type', ['text', 'html', 'banner', 'promotion', 'newsletter'])
                ->default('html')
                ->comment('Type of block content');
            $table->longText('content')->nullable()->comment('JSON or HTML content based on type');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('channel_id')->nullable()->constrained('channels')->onDelete('cascade');
            $table->dateTime('start_date')->nullable()->comment('Scheduled start date for block visibility');
            $table->dateTime('end_date')->nullable()->comment('Scheduled end date for block visibility');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('identifier');
            $table->index('status');
            $table->index('type');
            $table->index(['start_date', 'end_date']);
            $table->index('channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
