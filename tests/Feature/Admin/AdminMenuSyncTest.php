<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Cartxis\Admin\Database\Seeders\AdminMenuSeeder;
use Cartxis\Admin\Services\AdminMenuSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AdminMenuSeeder::class);
});

test('admin menu sync nests newsletters under marketing', function () {
    $marketingId = DB::table('menu_items')->where('key', 'marketing')->value('id');

    DB::table('menu_items')
        ->where('key', 'marketing-newsletters')
        ->update(['parent_id' => null]);

    app(AdminMenuSyncService::class)->sync();

    $newsletter = DB::table('menu_items')
        ->where('key', 'marketing-newsletters')
        ->first();

    expect($newsletter)->not->toBeNull()
        ->and((int) $newsletter->parent_id)->toBe((int) $marketingId);
});

test('admin menu sync restores browse themes under appearance', function () {
    DB::table('menu_items')->where('key', 'appearance-template-zone')->delete();

    app(AdminMenuSyncService::class)->sync();

    $appearanceId = DB::table('menu_items')->where('key', 'appearance')->value('id');
    $templateZone = DB::table('menu_items')->where('key', 'appearance-template-zone')->first();

    expect($templateZone)->not->toBeNull()
        ->and($templateZone->title)->toBe('Browse Themes')
        ->and($templateZone->route)->toBe('admin.template-zone.index')
        ->and((int) $templateZone->parent_id)->toBe((int) $appearanceId)
        ->and((bool) $templateZone->active)->toBeTrue();
});
