<?php

declare(strict_types=1);

namespace Tests\Feature\I18n;

use Cartxis\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product translations resolve for active locale with english fallback', function () {
    $product = Product::query()->create([
        'name' => 'Red Shirt',
        'slug' => 'red-shirt',
        'sku' => 'SKU-RED-1',
        'price' => 19.99,
        'status' => 'enabled',
        'visibility' => 'both',
        'quantity' => 10,
        'stock_status' => 'in_stock',
        'type' => 'simple',
    ]);

    $product->syncTranslations([
        'ar' => [
            'name' => 'قميص أحمر',
            'slug' => 'qamis-ahmar',
            'short_description' => 'وصف قصير',
        ],
    ]);

    app()->setLocale('ar');
    $product->load('translations')->translate('ar');

    expect($product->name)->toBe('قميص أحمر')
        ->and($product->slug)->toBe('qamis-ahmar');

    app()->setLocale('fr');
    $fresh = Product::query()->with('translations')->findOrFail($product->id);
    $fresh->translate('fr');

    expect($fresh->name)->toBe('Red Shirt');
});
