<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uieditor_saved_blocks', function (Blueprint $table) {
            $table->boolean('is_builtin')
                ->default(false)
                ->after('type')
                ->comment('true = shipped with the system (seeded); false = created by user');
        });
    }

    public function down(): void
    {
        Schema::table('uieditor_saved_blocks', function (Blueprint $table) {
            $table->dropColumn('is_builtin');
        });
    }
};
