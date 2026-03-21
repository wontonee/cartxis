<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('code');
            $table->index('is_default');
        });

        // Now that tax_classes exists, add the FK on products.tax_class_id
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('tax_class_id')->references('id')->on('tax_classes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_class_id']);
        });

        Schema::dropIfExists('tax_classes');
    }
};
