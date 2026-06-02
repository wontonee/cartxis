<?php

use Cartxis\Core\Services\RemoteThemeDirectoryClient;
use Cartxis\Core\Services\TemplateCatalogService;
use Cartxis\Core\Services\ThemeDirectoryRegistrationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('theme.directory.url', 'https://themes.test/api');
    Config::set('theme.directory.api_key', 'ctx_test_api_key');
    Config::set('theme.directory.cache_ttl', 60);
});

test('remote theme directory does not cache failed fetch responses', function () {
    Config::set('theme.directory.api_key', null);

    Http::fakeSequence()
        ->push('Server Error', 500)
        ->push([
            'data' => [
                [
                    'slug' => 'dmart-electronics',
                    'name' => 'Dmart Electronics',
                    'version' => '1.0.0',
                    'description' => 'Electronics theme',
                    'author' => 'Cartxis',
                    'supports' => [],
                    'categories' => [['slug' => 'electronics', 'name' => 'Electronics']],
                    'screenshot_url' => 'https://themes.test/preview.png',
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
        ], 200);

    $client = app(RemoteThemeDirectoryClient::class);
    $client->clearCache();

    expect($client->fetchThemes()['data'])->toBe([]);

    expect($client->fetchThemes()['data'])->toHaveCount(1);
});

test('remote theme directory client fetches categories and themes without api key for browse', function () {
    Config::set('theme.directory.api_key', null);

    Http::fake([
        'themes.test/api/theme-categories' => Http::response([
            'data' => [
                ['slug' => 'featured', 'name' => 'Featured', 'sort_order' => 1],
            ],
        ]),
        'themes.test/api/themes*' => Http::response([
            'data' => [
                [
                    'slug' => 'fashion-pro',
                    'name' => 'Fashion Pro',
                    'version' => '1.0.0',
                    'description' => 'A fashion storefront theme',
                    'author' => 'Cartxis',
                    'supports' => ['menus'],
                    'categories' => [['slug' => 'featured', 'name' => 'Featured']],
                    'screenshot_url' => 'https://themes.test/storage/previews/fashion-pro.png',
                    'updated_at' => '2026-05-30T00:00:00Z',
                ],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
        ]),
    ]);

    $client = app(RemoteThemeDirectoryClient::class);

    expect($client->canBrowse())->toBeTrue()
        ->and($client->canInstall())->toBeFalse()
        ->and($client->fetchCategories())->toHaveCount(1)
        ->and($client->fetchThemes('featured', 'fashion')['data'])->toHaveCount(1);
});

test('template catalog merges remote themes with local catalog', function () {
    Http::fake([
        'themes.test/api/theme-categories' => Http::response(['data' => []]),
        'themes.test/api/themes*' => Http::response([
            'data' => [
                [
                    'slug' => 'remote-theme',
                    'name' => 'Remote Theme',
                    'version' => '2.0.0',
                    'description' => 'From directory',
                    'author' => 'Cartxis',
                    'supports' => [],
                    'categories' => [['slug' => 'general', 'name' => 'General']],
                    'screenshot_url' => 'https://themes.test/preview.png',
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
        ]),
    ]);

    $catalog = app(TemplateCatalogService::class);
    $remote = $catalog->discover('storefront')->firstWhere('slug', 'remote-theme');

    expect($remote)->not->toBeNull()
        ->and($remote['source'])->toBe('remote')
        ->and($remote['screenshot_url'])->toBe('https://themes.test/preview.png')
        ->and($catalog->isRemoteEntry($remote))->toBeTrue()
        ->and($catalog->isRemoteBrowseAvailable())->toBeTrue()
        ->and($catalog->isRemoteInstallAvailable())->toBeTrue();
});

test('template catalog passes search query to remote directory', function () {
    Http::fake([
        'themes.test/api/theme-categories' => Http::response(['data' => []]),
        'themes.test/api/themes*' => function ($request) {
            expect($request->data()['search'] ?? null)->toBe('boutique');

            return Http::response([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ]);
        },
    ]);

    app(TemplateCatalogService::class)->discover('storefront', null, 'boutique');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/themes'));
});

test('theme directory registration service saves key from directory api', function () {
    Http::fake([
        'themes.test/api/theme-keys/register' => Http::response([
            'plain_text_key' => 'ctx_generated_install_key_123456789012345678901234',
            'key_prefix' => 'ctx_gene',
        ], 201),
    ]);

    Config::set('theme.directory.url', 'https://themes.test/api');

    $key = app(ThemeDirectoryRegistrationService::class)
        ->register('Demo Store', 'https://demo.test');

    expect($key)->toBe('ctx_generated_install_key_123456789012345678901234');
});

test('remote theme download verifies package hash header', function () {
    $zipContents = file_get_contents(createRemoteThemeZipForTest());
    $hash = hash('sha256', $zipContents);

    Http::fake([
        'themes.test/api/themes/remote-theme/install' => Http::response($zipContents, 200, [
            'Content-Type' => 'application/zip',
            'X-Theme-Hash' => $hash,
        ]),
    ]);

    $download = app(RemoteThemeDirectoryClient::class)->download('remote-theme');

    expect(file_exists($download['path']))->toBeTrue()
        ->and($download['hash'])->toBe($hash);

    @unlink($download['path']);
});

function createRemoteThemeZipForTest(): string
{
    $tempPath = tempnam(sys_get_temp_dir(), 'remote-theme-');
    unlink($tempPath);

    $zip = new ZipArchive;
    $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('theme.json', json_encode([
        'name' => 'Remote Theme',
        'slug' => 'remote-theme',
        'version' => '1.0.0',
    ], JSON_PRETTY_PRINT));
    $zip->close();

    return $tempPath;
}
