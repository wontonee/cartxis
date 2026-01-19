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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            
            // Template Info
            $table->string('code', 100)->comment('order_placed, password_reset, etc');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable()->comment('order, account, payment, shipping, marketing');
            
            // Email Settings
            $table->string('subject', 500);
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('cc', 500)->nullable();
            $table->string('bcc', 500)->nullable();
            
            // Content
            $table->longText('html_content');
            $table->longText('plain_text_content')->nullable();
            
            // Variables Used
            $table->json('variables')->nullable()->comment('["customer_name", "order_number", etc]');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment("Can't be deleted if TRUE");
            $table->string('locale', 10)->default('en');
            
            // Metadata
            $table->timestamp('last_sent_at')->nullable();
            $table->integer('times_sent')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['code', 'locale']);
            $table->index('code');
            $table->index('is_active');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
