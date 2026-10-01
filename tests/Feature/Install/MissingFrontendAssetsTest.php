<?php

declare(strict_types=1);

use Cartxis\Core\Support\FrontendAssetManifest;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\ViteManifestNotFoundException;

test('frontend asset manifest reports present when file exists', function () {
    $dir = sys_get_temp_dir().'/cartxis-assets-'.uniqid('', true);
    mkdir($dir.'/build', 0755, true);
    file_put_contents($dir.'/build/manifest.json', '{}');

    expect(FrontendAssetManifest::exists($dir))->toBeTrue()
        ->and(FrontendAssetManifest::path($dir))->toEndWith('build'.DIRECTORY_SEPARATOR.'manifest.json');

    unlink($dir.'/build/manifest.json');
    rmdir($dir.'/build');
    rmdir($dir);
});

test('frontend asset manifest reports missing when file absent', function () {
    $dir = sys_get_temp_dir().'/cartxis-assets-missing-'.uniqid('', true);
    mkdir($dir, 0755, true);

    expect(FrontendAssetManifest::exists($dir))->toBeFalse();

    rmdir($dir);
});

test('vite manifest not found renders friendly shared hosting page with 503', function () {
    $exception = new ViteManifestNotFoundException('Vite manifest not found at: /tmp/build/manifest.json');

    $response = app(ExceptionHandler::class)->render(request(), $exception);

    expect($response->getStatusCode())->toBe(503);

    $html = $response->getContent();

    expect($html)
        ->toContain('Frontend assets are missing')
        ->toContain('Shared Hosting')
        ->toContain('npm install')
        ->not->toContain('@vite');
});
