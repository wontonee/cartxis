<?php

use Cartxis\Core\Models\Setting;
use Cartxis\Core\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists extension_code when setting a value and can delete by extension', function () {
    /** @var SettingService $settings */
    $settings = app(SettingService::class);

    $settings->set('test.extension.flag', 'yes', 'string', 'test', 'cartxis-test-ext');

    $row = Setting::where('key', 'test.extension.flag')->first();
    expect($row)->not->toBeNull()
        ->and($row->extension_code)->toBe('cartxis-test-ext')
        ->and($settings->get('test.extension.flag'))->toBe('yes');

    $settings->deleteByExtension('cartxis-test-ext');

    expect(Setting::where('key', 'test.extension.flag')->exists())->toBeFalse();
});
