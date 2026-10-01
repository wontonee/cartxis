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

test('admin menu sync adds MCP under settings', function () {
    DB::table('menu_items')->where('key', 'settings-mcp')->delete();

    app(AdminMenuSyncService::class)->sync();

    $settingsId = DB::table('menu_items')->where('key', 'settings')->value('id');
    $mcp = DB::table('menu_items')->where('key', 'settings-mcp')->first();

    expect($mcp)->not->toBeNull()
        ->and($mcp->title)->toBe('MCP')
        ->and($mcp->route)->toBe('admin.settings.mcp.index')
        ->and((int) $mcp->parent_id)->toBe((int) $settingsId)
        ->and((bool) $mcp->active)->toBeTrue();
});

test('admin menu sync adds Quote Requests under catalog', function () {
    DB::table('menu_items')->where('key', 'catalog-quote-requests')->delete();
    DB::table('menu_items')->where('key', 'catalog-reviews')->update(['order' => 5]);

    app(AdminMenuSyncService::class)->sync();

    $catalogId = DB::table('menu_items')->where('key', 'catalog')->value('id');
    $quote = DB::table('menu_items')->where('key', 'catalog-quote-requests')->first();
    $reviews = DB::table('menu_items')->where('key', 'catalog-reviews')->first();

    expect($quote)->not->toBeNull()
        ->and($quote->title)->toBe('Quote Requests')
        ->and($quote->route)->toBe('admin.catalog.quote-requests.index')
        ->and((int) $quote->parent_id)->toBe((int) $catalogId)
        ->and((int) $quote->order)->toBe(5)
        ->and((bool) $quote->active)->toBeTrue();

    expect($reviews)->not->toBeNull()
        ->and((int) $reviews->order)->toBe(6);
});

test('admin menu seeder includes MCP and Quote Requests', function () {
    $settingsId = DB::table('menu_items')->where('key', 'settings')->value('id');
    $catalogId = DB::table('menu_items')->where('key', 'catalog')->value('id');

    $mcp = DB::table('menu_items')->where('key', 'settings-mcp')->first();
    $quote = DB::table('menu_items')->where('key', 'catalog-quote-requests')->first();

    expect($mcp)->not->toBeNull()
        ->and($mcp->title)->toBe('MCP')
        ->and($mcp->route)->toBe('admin.settings.mcp.index')
        ->and((int) $mcp->parent_id)->toBe((int) $settingsId)
        ->and((bool) $mcp->active)->toBeTrue();

    expect($quote)->not->toBeNull()
        ->and($quote->title)->toBe('Quote Requests')
        ->and($quote->route)->toBe('admin.catalog.quote-requests.index')
        ->and((int) $quote->parent_id)->toBe((int) $catalogId)
        ->and((bool) $quote->active)->toBeTrue();
});
