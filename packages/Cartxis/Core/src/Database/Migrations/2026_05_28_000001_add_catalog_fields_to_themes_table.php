<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('catalog_slug')->nullable()->after('slug');
            $table->string('source')->default('upload')->after('is_default');
            $table->string('category')->nullable()->after('source');
            $table->timestamp('installed_from_catalog_at')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'catalog_slug',
                'source',
                'category',
                'installed_from_catalog_at',
            ]);
        });
    }
};
