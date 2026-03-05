<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uieditor_global_regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->enum('region_type', ['header', 'footer', 'section', 'banner', 'sidebar'])
                ->default('section')
                ->comment('Logical category for the region');
            $table->json('layout_data')->nullable()->comment('Draft layout JSON (same format as page layouts)');
            $table->json('published_layout_data')->nullable()->comment('Last published snapshot, served to the storefront');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('region_type');
            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uieditor_global_regions');
    }
};
