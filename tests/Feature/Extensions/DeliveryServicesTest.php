<?php

use Cartxis\Delivery\Models\Delivery;
use Cartxis\Delivery\Models\Zone;
use Cartxis\Delivery\Providers\DeliveryServiceProvider;
use Cartxis\Delivery\Services\DeliveryService;
use Cartxis\Delivery\Services\ZoneService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    if (! File::isDirectory(base_path('extension/cartxis-delivery'))) {
        $this->markTestSkipped('cartxis-delivery extension not installed in this workspace.');
    }

    if (! class_exists(ZoneService::class)) {
        $providerFile = base_path('extension/cartxis-delivery/src/Providers/DeliveryServiceProvider.php');
        require_once $providerFile;
        (new DeliveryServiceProvider(app()))->register();
    }

    if (! class_exists(ZoneService::class)) {
        $this->markTestSkipped('Cartxis\\Delivery classes could not be autoloaded.');
    }
});

test('zone service matches postcodes case-insensitively', function () {
    $zone = new Zone([
        'name' => 'Central',
        'type' => 'postcodes',
        'postcodes' => ['110001', 'SW1A 1AA'],
        'active' => true,
    ]);

    $matched = (new ZoneService)->findMatchingZone(
        ['postal_code' => 'sw1a1aa'],
        collect([$zone])
    );

    expect($matched)->not->toBeNull()
        ->and($matched->name)->toBe('Central');
});

test('delivery service exposes allowed status transitions', function () {
    $service = new DeliveryService(new ZoneService);
    $delivery = new Delivery(['status' => Delivery::STATUS_PLACED]);

    expect($service->canTransition($delivery, Delivery::STATUS_PACKED))->toBeTrue()
        ->and($service->canTransition($delivery, Delivery::STATUS_DELIVERED))->toBeFalse();
});
