<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_homepage flag to pages table
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_homepage')->default(false)->after('status')->index();
        });

        // Seed the Homepage page (only if none exists)
        $exists = DB::table('pages')->where('is_homepage', true)->exists();

        if (! $exists) {
            DB::table('pages')->insert([
                'title'            => 'Homepage',
                'url_key'          => 'home',
                'content'          => '',
                'meta_title'       => 'Home',
                'meta_description' => null,
                'meta_keywords'    => null,
                'status'           => 'published',
                'is_homepage'      => true,
                'created_by'       => null,
                'updated_by'       => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('pages')->where('is_homepage', true)->delete();

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('is_homepage');
        });
    }
};
