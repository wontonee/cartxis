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
        Schema::table('products', function (Blueprint $table) {
            // Add tax_class_id after price column
            $table->foreignId('tax_class_id')
                ->nullable()
                ->after('price')
                ->constrained('tax_classes')
                ->onDelete('set null');
            
            $table->index('tax_class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_class_id']);
            $table->dropIndex(['tax_class_id']);
            $table->dropColumn('tax_class_id');
        });
    }
};
