<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uieditor_saved_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['section', 'block'])->default('section')
                ->comment('section = full row with columns; block = single content block');
            $table->longText('layout_data')
                ->comment('JSON: section object or single block object');
            $table->string('thumbnail')->nullable()
                ->comment('URL to preview thumbnail (future use)');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uieditor_saved_blocks');
    }
};
