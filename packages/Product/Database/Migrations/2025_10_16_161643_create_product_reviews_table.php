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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reviewer_name')->nullable(); // For guest reviews (future)
            $table->string('reviewer_email')->nullable(); // For guest reviews (future)
            $table->unsignedTinyInteger('rating')->comment('1-5 stars');
            $table->string('title', 255);
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_reply')->nullable();
            $table->foreignId('admin_reply_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('admin_replied_at')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->boolean('verified_purchase')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('product_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('rating');
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
