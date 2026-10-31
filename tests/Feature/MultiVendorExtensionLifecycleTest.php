<?php

/**
 * Feature wrapper: skips when commercial extension is absent from disk / DB.
 */

use Cartxis\Core\Models\Extension;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

test('multivendor commercial extension lifecycle smoke', function () {
    $path = base_path('extension/cartxis-multivendor/extension.json');

    if (! File::exists($path)) {
        $this->markTestSkipped('cartxis-multivendor not present (commercial / gitignored).');
    }

    $manifest = json_decode(File::get($path), true);
    expect($manifest['code'])->toBe('cartxis-multivendor');

    require_once base_path('extension/cartxis-multivendor/src/Providers/MultiVendorServiceProvider.php');
    expect(class_exists(\Cartxis\MultiVendor\Providers\MultiVendorServiceProvider::class))->toBeTrue();

    $svc = base_path('extension/cartxis-multivendor/src/Services/CommissionService.php');
    require_once $svc;
    $math = (new \Cartxis\MultiVendor\Services\CommissionService())->calculate(200, 12.5);
    expect($math['commission_amount'])->toBe(25.0)
        ->and($math['net_amount'])->toBe(175.0);

    if (Schema::hasTable('extensions')) {
        $this->artisan('cartxis:extensions:sync')->assertSuccessful();
        expect(Extension::where('code', 'cartxis-multivendor')->exists())->toBeTrue();
    }
});
