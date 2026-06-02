<?php

use Cartxis\Core\Services\ThemeDirectoryEnvConfigurator;
use Cartxis\Core\Services\ThemeDirectoryRegistrationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

function themeDirectoryTestEnvPath(): string
{
    return storage_path('framework/testing-theme-env-'.uniqid('', true).'.env');
}

test('theme directory configurator registers api key into env file on fresh install', function () {
    Http::fake([
        'themes.test/api/theme-keys/register' => Http::response([
            'plain_text_key' => 'ctx_generated_install_key_123456789012345678901234',
            'key_prefix' => 'ctx_gene',
        ], 201),
    ]);

    Config::set('theme.directory.url', 'https://themes.test/api');
    Config::set('theme.directory.api_key', null);

    $envPath = themeDirectoryTestEnvPath();
    file_put_contents($envPath, "APP_NAME=Demo Store\nAPP_URL=https://demo.test\n");

    $configurator = new ThemeDirectoryEnvConfigurator(
        app(ThemeDirectoryRegistrationService::class),
        $envPath,
    );

    $result = $configurator->ensureConfigured('Demo Store', 'https://demo.test');

    expect($result['status'])->toBe('registered')
        ->and($result['api_key'])->toBe('ctx_generated_install_key_123456789012345678901234');

    $content = file_get_contents($envPath);

    expect($content)
        ->toContain('CARTXIS_THEME_DIRECTORY_URL=https://themes.test/api')
        ->toContain('CARTXIS_THEME_API_KEY=ctx_generated_install_key_123456789012345678901234');

    @unlink($envPath);
});

test('theme directory configurator skips registration when api key already exists', function () {
    Http::fake();

    Config::set('theme.directory.url', 'https://themes.test/api');
    Config::set('theme.directory.api_key', null);

    $envPath = themeDirectoryTestEnvPath();
    file_put_contents($envPath, "CARTXIS_THEME_API_KEY=ctx_existing_key_123456789012345678901234\n");

    $configurator = new ThemeDirectoryEnvConfigurator(
        app(ThemeDirectoryRegistrationService::class),
        $envPath,
    );

    $result = $configurator->ensureConfigured('Demo Store', 'https://demo.test');

    expect($result['status'])->toBe('already_configured');

    Http::assertNothingSent();

    @unlink($envPath);
});

test('theme directory configurator treats empty api key env value as not configured', function () {
    Http::fake([
        'themes.test/api/theme-keys/register' => Http::response([
            'plain_text_key' => 'ctx_new_key_after_empty_123456789012345678901234',
            'key_prefix' => 'ctx_new',
        ], 201),
    ]);

    Config::set('theme.directory.url', 'https://themes.test/api');
    Config::set('theme.directory.api_key', null);

    $envPath = themeDirectoryTestEnvPath();
    file_put_contents($envPath, "CARTXIS_THEME_API_KEY=\n");

    $configurator = new ThemeDirectoryEnvConfigurator(
        app(ThemeDirectoryRegistrationService::class),
        $envPath,
    );

    expect($configurator->isApiKeyConfigured())->toBeFalse();

    $result = $configurator->ensureConfigured('Demo Store', 'https://demo.test');

    expect($result['status'])->toBe('registered')
        ->and(file_get_contents($envPath))->toContain('ctx_new_key_after_empty_123456789012345678901234');

    @unlink($envPath);
});

test('theme directory register command reports success when key already configured', function () {
    Config::set('theme.directory.url', 'https://themes.test/api');
    Config::set('theme.directory.api_key', 'ctx_existing_key_123456789012345678901234');

    $this->artisan('theme:directory:register')
        ->expectsOutputToContain('already saved in .env')
        ->assertSuccessful();
});
