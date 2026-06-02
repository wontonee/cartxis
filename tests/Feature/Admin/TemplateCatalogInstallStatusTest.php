<?php

declare(strict_types=1);

use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\TemplateCatalogService;
use Cartxis\Core\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('catalog-only storefront packages are not marked installed until explicitly installed', function () {
    $categoryPath = base_path('templates/storefront/electronics');
    $packagePath = $categoryPath.'/dmart-electronics';

    File::ensureDirectoryExists($packagePath.'/resources/views/pages');
    File::ensureDirectoryExists($packagePath.'/resources/views/components');
    File::ensureDirectoryExists($packagePath.'/resources/views/layouts');

    File::put($packagePath.'/theme.json', json_encode([
        'name' => 'Dmart Electronics',
        'slug' => 'dmart-electronics',
        'version' => '1.0.0',
        'author' => 'Cartxis',
    ], JSON_PRETTY_PRINT));

    File::put($packagePath.'/template.json', json_encode([
        'slug' => 'dmart-electronics',
        'type' => 'storefront',
        'category' => 'electronics',
        'name' => 'Dmart Electronics',
        'description' => 'Electronics demo theme',
        'version' => '1.0.0',
        'author' => 'Cartxis',
    ], JSON_PRETTY_PRINT));

    app(ThemeService::class)->discover();

    expect(Theme::where('slug', 'dmart-electronics')->exists())->toBeFalse();

    $catalog = app(TemplateCatalogService::class);
    $entry = $catalog->discover('storefront')->firstWhere('slug', 'dmart-electronics');

    expect($entry)->not->toBeNull()
        ->and($entry['installed'])->toBeFalse()
        ->and($catalog->isInstalled('dmart-electronics'))->toBeFalse()
        ->and($catalog->isInstalled('cartxis-default'))->toBeTrue();

    File::deleteDirectory($categoryPath);
});
