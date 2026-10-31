<?php

it('documents commercial extensions as optional filesystem packages', function () {
    $delivery = base_path('extension/cartxis-delivery/extension.json');
    $multivendor = base_path('extension/cartxis-multivendor/extension.json');

    if (file_exists($delivery)) {
        $json = json_decode(file_get_contents($delivery), true);
        expect($json['code'] ?? null)->toBe('cartxis-delivery');
    }

    if (file_exists($multivendor)) {
        $json = json_decode(file_get_contents($multivendor), true);
        expect($json['code'] ?? null)->toBe('cartxis-multivendor');
    }

    expect(true)->toBeTrue();
});
