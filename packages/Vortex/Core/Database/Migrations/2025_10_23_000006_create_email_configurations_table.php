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
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            
            // General Settings
            $table->string('mail_driver', 50)->default('smtp')->comment('smtp, ses, postmark, sendmail, log');
            $table->string('mail_from_address');
            $table->string('mail_from_name');
            
            // SMTP Settings
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable()->comment('Encrypted');
            $table->string('smtp_encryption', 50)->nullable()->comment('tls, ssl, none');
            
            // Amazon SES Settings
            $table->string('ses_key')->nullable()->comment('Encrypted');
            $table->text('ses_secret')->nullable()->comment('Encrypted');
            $table->string('ses_region', 50)->nullable();
            
            // Postmark Settings
            $table->text('postmark_token')->nullable()->comment('Encrypted');
            
            // Sendmail Settings
            $table->string('sendmail_path', 500)->nullable();
            
            // Additional Settings
            $table->string('reply_to_email')->nullable();
            $table->string('bcc_email')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Testing
            $table->timestamp('last_test_at')->nullable();
            $table->string('last_test_status', 50)->nullable()->comment('success, failed');
            $table->text('last_test_message')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('mail_driver');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configurations');
    }
};
