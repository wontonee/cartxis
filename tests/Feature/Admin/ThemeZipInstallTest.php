<?php

declare(strict_types=1);

use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\TemplateInstallService;
use Cartxis\Core\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('theme zip with single root folder installs to correct storefront path', function () {
    $categoryPath = base_path('templates/storefront/electronics');
    $packagePath = $categoryPath.'/dmart-electronics';
    $zipPath = createNestedThemeZipForTest('dmart-electronics');

    File::deleteDirectory($categoryPath);

    $installedSlug = app(ThemeService::class)->install($zipPath, 'electronics');

    expect($installedSlug)->toBe('dmart-electronics')
        ->and(file_exists($packagePath.'/theme.json'))->toBeTrue()
        ->and(file_exists($packagePath.'/dmart-electronics/theme.json'))->toBeFalse();

    app(ThemeService::class)->discover();

    expect(Theme::where('slug', 'dmart-electronics')->exists())->toBeTrue();

    File::deleteDirectory($categoryPath);
    @unlink($zipPath);
});

test('discover flattens previously nested theme packages', function () {
    $categoryPath = base_path('templates/storefront/electronics');
    $packagePath = $categoryPath.'/dmart-electronics';
    $nestedPath = $packagePath.'/dmart-electronics';

    File::deleteDirectory($categoryPath);
    File::ensureDirectoryExists($nestedPath.'/resources/views/pages');
    File::ensureDirectoryExists($nestedPath.'/resources/views/components');
    File::ensureDirectoryExists($nestedPath.'/resources/views/layouts');
    File::put($nestedPath.'/theme.json', json_encode([
        'name' => 'Dmart Electronics',
        'slug' => 'dmart-electronics',
        'version' => '1.0.0',
    ], JSON_PRETTY_PRINT));

    app(ThemeService::class)->discover();

    expect(file_exists($packagePath.'/theme.json'))->toBeTrue()
        ->and(Theme::where('slug', 'dmart-electronics')->exists())->toBeTrue();

    File::deleteDirectory($categoryPath);
});

test('remote catalog install registers theme even when template.json is present', function () {
    $categoryPath = base_path('templates/storefront/electronics');
    $packagePath = $categoryPath.'/dmart-electronics';
    $zipPath = createNestedThemeZipForTest('dmart-electronics', withTemplateJson: true);

    File::deleteDirectory($categoryPath);

    $installedSlug = app(ThemeService::class)->install($zipPath, 'electronics');

    expect($installedSlug)->toBe('dmart-electronics');

    app(TemplateInstallService::class)->install('dmart-electronics', [
        'activate' => false,
    ]);

    expect(Theme::where('slug', 'dmart-electronics')->value('installed_from_catalog_at'))->not->toBeNull();

    File::deleteDirectory($categoryPath);
    @unlink($zipPath);
});

function createNestedThemeZipForTest(string $slug, bool $withTemplateJson = false): string
{
    $tempPath = tempnam(sys_get_temp_dir(), 'theme-zip-');
    unlink($tempPath);

    $zip = new ZipArchive;
    $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString("{$slug}/theme.json", json_encode([
        'name' => 'Dmart Electronics',
        'slug' => $slug,
        'version' => '1.0.0',
        'author' => 'Cartxis',
    ], JSON_PRETTY_PRINT));
    $zip->addFromString("{$slug}/resources/views/pages/Home.vue", '<template><div /></template>');
    $zip->addFromString("{$slug}/resources/views/components/Header.vue", '<template><div /></template>');
    $zip->addFromString("{$slug}/resources/views/layouts/ThemeLayout.vue", '<template><div /></template>');

    if ($withTemplateJson) {
        $zip->addFromString("{$slug}/template.json", json_encode([
            'slug' => $slug,
            'type' => 'storefront',
            'category' => 'electronics',
            'name' => 'Dmart Electronics',
            'version' => '1.0.0',
        ], JSON_PRETTY_PRINT));
    }

    $zip->close();

    return $tempPath;
}
