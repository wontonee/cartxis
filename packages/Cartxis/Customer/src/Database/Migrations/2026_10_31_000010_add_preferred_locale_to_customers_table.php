<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'preferred_locale')) {
                $table->string('preferred_locale', 10)->nullable()->after('newsletter_subscribed');
                $table->index('preferred_locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'preferred_locale')) {
                $table->dropIndex(['preferred_locale']);
                $table->dropColumn('preferred_locale');
            }
        });
    }
};
