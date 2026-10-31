<?php

use Illuminate\Support\Facades\File;

test('cartxis-delivery extension manifest is valid when present', function () {
    $extensionPath = base_path('extension/cartxis-delivery');

    if (! File::isDirectory($extensionPath)) {
        $this->markTestSkipped('cartxis-delivery extension not installed in this workspace.');
    }

    $manifestPath = $extensionPath.'/extension.json';
    expect(File::exists($manifestPath))->toBeTrue();

    $manifest = json_decode(File::get($manifestPath), true);
    expect($manifest)->toBeArray()
        ->and($manifest['code'])->toBe('cartxis-delivery')
        ->and($manifest['category'])->toBe('fulfillment')
        ->and($manifest['provider'])->toBe('Cartxis\\Delivery\\Providers\\DeliveryServiceProvider')
        ->and($manifest['provider_file'] ?? null)->toBe('src/Providers/DeliveryServiceProvider.php');

    expect(File::exists($extensionPath.'/'.$manifest['provider_file']))->toBeTrue();
    expect(File::exists($extensionPath.'/src/Database/Migrations/2026_10_31_100001_create_dlv_zones_table.php'))->toBeTrue();
    expect(File::exists($extensionPath.'/src/Services/DeliveryService.php'))->toBeTrue();
});

test('cartxis-delivery migrations include vendor_id stub on zones', function () {
    $extensionPath = base_path('extension/cartxis-delivery');

    if (! File::isDirectory($extensionPath)) {
        $this->markTestSkipped('cartxis-delivery extension not installed in this workspace.');
    }

    $migration = File::get(
        $extensionPath.'/src/Database/Migrations/2026_10_31_100001_create_dlv_zones_table.php'
    );

    expect($migration)->toContain('vendor_id')
        ->and($migration)->toContain('dlv_zones');
});
