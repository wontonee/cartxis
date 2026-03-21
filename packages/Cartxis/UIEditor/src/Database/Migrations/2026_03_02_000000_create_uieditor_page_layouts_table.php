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
        Schema::create('uieditor_page_layouts', function (Blueprint $table) {
            $table->id();
            $table->enum('page_type', ['cms_page', 'homepage'])
                ->comment('Context: cms_page or homepage');
            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
                ->onDelete('cascade')
                ->comment('FK to pages table; null for homepage type');
            $table->longText('layout_data')
                ->comment('JSON tree: sections → columns → blocks');
            $table->enum('status', ['draft', 'published'])
                ->default('draft')
                ->comment('draft = not visible to public; published = live');
            $table->timestamp('published_at')->nullable()->comment('Timestamp of last publish action');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('page_type');
            $table->index('page_id');
            $table->index('status');
            $table->index(['page_type', 'page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uieditor_page_layouts');
    }
};
