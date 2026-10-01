<?php

use Cartxis\Cart\Support\CartTypeHelper;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\QuoteRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

function makeCatalogProduct(array $overrides = []): Product
{
    return Product::create(array_merge([
        'name' => 'Test Product '.Str::random(5),
        'sku' => 'SKU-'.strtoupper(Str::random(6)),
        'slug' => 'test-product-'.Str::lower(Str::random(6)),
        'price' => 10,
        'status' => 'enabled',
        'visibility' => 'both',
        'quantity' => 10,
        'stock_status' => 'in_stock',
        'manage_stock' => true,
        'type' => Product::TYPE_SIMPLE,
    ], $overrides));
}

test('cart type helper detects digital-only carts', function () {
    expect(CartTypeHelper::isDigitalOnly([
        ['type' => 'virtual', 'requires_shipping' => false],
        ['type' => 'downloadable', 'requires_shipping' => false],
    ]))->toBeTrue();

    expect(CartTypeHelper::cartRequiresShipping([
        ['type' => 'simple', 'requires_shipping' => true],
        ['type' => 'virtual', 'requires_shipping' => false],
    ]))->toBeTrue();
});

test('quote products cannot be added to the cart', function () {
    $product = makeCatalogProduct([
        'type' => Product::TYPE_QUOTE,
        'manage_stock' => false,
        'quantity' => 0,
        'price' => 0,
    ]);

    $response = $this->postJson('/api/cart/add', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(400);
});

test('customers can submit a quote request for quote products', function () {
    $product = makeCatalogProduct([
        'type' => Product::TYPE_QUOTE,
        'slug' => 'custom-print-job',
        'manage_stock' => false,
        'price' => 0,
    ]);

    $response = $this->post(route('shop.products.quote', $product->slug), [
        'customer_name' => 'Jane Buyer',
        'customer_email' => 'jane@example.com',
        'quantity' => 25,
        'message' => 'Need bulk pricing',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('quote_requests', [
        'product_id' => $product->id,
        'customer_email' => 'jane@example.com',
        'quantity' => 25,
        'status' => QuoteRequest::STATUS_NEW,
    ]);
});

test('virtual products snapshot type onto session cart lines', function () {
    $product = makeCatalogProduct([
        'type' => Product::TYPE_VIRTUAL,
        'manage_stock' => false,
        'quantity' => 10,
        'price' => 19.99,
    ]);

    $this->post('/cart/add', [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    $cart = Session::get('cart', []);
    expect($cart)->not->toBeEmpty();
    expect($cart[0]['type'])->toBe('virtual');
    expect($cart[0]['requires_shipping'])->toBeFalse();
});
