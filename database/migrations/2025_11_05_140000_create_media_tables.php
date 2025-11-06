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
        // Create media_folders table first (for foreign key)
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->onDelete('cascade');
            $table->string('path')->nullable(); // Full path like /folder1/subfolder2
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('path');
        });

        // Create media_files table
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Stored filename (UUID-based)
            $table->string('original_filename'); // Original upload name
            $table->string('path'); // Storage path
            $table->string('disk')->default('public'); // Storage disk (public, s3, etc.)
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('extension', 10)->nullable();
            
            // Relationships
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->onDelete('set null');
            
            // SEO & Accessibility
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Image-specific fields
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            
            // Thumbnails stored as JSON: {"150": "path/to/thumb-150.jpg", "600": "path/to/thumb-600.jpg"}
            $table->json('thumbnails')->nullable();
            
            // Tracking
            $table->unsignedInteger('used_count')->default(0); // How many times used
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('folder_id');
            $table->index('mime_type');
            $table->index('extension');
            $table->index('created_by');
            $table->index(['disk', 'path']);
            $table->fullText(['filename', 'original_filename', 'alt_text', 'title', 'description'], 'media_files_fulltext');
        });

        // Create media_usages table to track where files are used
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained('media_files')->onDelete('cascade');
            $table->morphs('usable'); // usable_type, usable_id (Page, Product, Category, etc.)
            $table->string('context')->nullable(); // 'featured_image', 'gallery', 'content', etc.
            $table->timestamps();

            $table->unique(['media_file_id', 'usable_type', 'usable_id', 'context'], 'media_usage_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_folders');
    }
};
