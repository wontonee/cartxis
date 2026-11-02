<?php

/**
 * Feature wrapper: Multivendor locale packs merge via LocaleService.
 * Skips when the commercial extension is absent.
 */

use Cartxis\Core\Services\LocaleService;
use Illuminate\Support\Facades\File;

test('multivendor locale packs merge into ui messages when present', function () {
    $en = base_path('extension/cartxis-multivendor/resources/locales/en.json');
    $manifest = base_path('extension/cartxis-multivendor/extension.json');

    if (! File::exists($en) || ! File::exists($manifest)) {
        $this->markTestSkipped('cartxis-multivendor locales not present (commercial / gitignored).');
    }

    $code = json_decode(File::get($manifest), true)['code'] ?? null;
    expect($code)->toBe('cartxis-multivendor');

    $service = app(LocaleService::class);
    expect($service->extensionLocaleNamespace($code))->toBe('cartxis_multivendor');

    $messages = $service->uiMessages('en');

    expect($messages['extensions']['cartxis_multivendor']['name'] ?? null)->toBe('Multi-Vendor')
        ->and($messages['extensions']['cartxis_multivendor']['vendor']['dashboard'] ?? null)->toBe('Dashboard')
        ->and($messages['admin']['menu']['multivendor'] ?? null)->toBe('Multi-Vendor');
});
