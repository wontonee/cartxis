<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all shop routes for the storefront.
| These routes are loaded by the ShopServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::group([
    'middleware' => config('shop.routes.middleware', ['web']),
    'prefix' => config('shop.routes.prefix', ''),
], function () {

    /*
    |--------------------------------------------------------------------------
    | Homepage
    |--------------------------------------------------------------------------
    */
    Route::get('/', [Cartxis\Shop\Http\Controllers\HomeController::class, 'index'])->name('shop.home');

    /*
    |--------------------------------------------------------------------------
    | Product Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/products', [Cartxis\Shop\Http\Controllers\ProductController::class, 'index'])->name('shop.products.index');
    Route::get('/products/{slug}/quick-view', [Cartxis\Shop\Http\Controllers\ProductController::class, 'quickView'])->name('shop.products.quick-view');
    Route::get('/product/{slug}', [Cartxis\Shop\Http\Controllers\ProductController::class, 'show'])->name('shop.products.show');

    /*
    |--------------------------------------------------------------------------
    | Category Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/category/{slug}', [Cartxis\Shop\Http\Controllers\CategoryController::class, 'show'])->name('shop.categories.show');

    /*
    |--------------------------------------------------------------------------
    | Search Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/search', [Cartxis\Shop\Http\Controllers\SearchController::class, 'index'])->name('shop.search');
    Route::get('/search/suggestions', [Cartxis\Shop\Http\Controllers\SearchController::class, 'suggestions'])->name('shop.search.suggestions');

    /*
    |--------------------------------------------------------------------------
    | Newsletter Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/newsletter/subscribe', [Cartxis\Shop\Http\Controllers\NewsletterController::class, 'subscribe'])->name('shop.newsletter.subscribe');
    Route::post('/newsletter/unsubscribe', [Cartxis\Shop\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('shop.newsletter.unsubscribe');

    /*
    |--------------------------------------------------------------------------
    | Customer Account Routes
    |--------------------------------------------------------------------------
    */
    Route::group([
        'prefix' => 'account',
        'middleware' => ['auth'],
        'as' => 'shop.account.'
    ], function () {
        Route::get('/', [Cartxis\Shop\Http\Controllers\Account\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [Cartxis\Shop\Http\Controllers\Account\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Cartxis\Shop\Http\Controllers\Account\OrderController::class, 'show'])->name('orders.show');
        Route::get('/addresses', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default-shipping', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'setDefaultShipping'])->name('addresses.default-shipping');
        Route::post('/addresses/{address}/default-billing', [Cartxis\Shop\Http\Controllers\Account\AddressController::class, 'setDefaultBilling'])->name('addresses.default-billing');
        Route::get('/wishlist', [Cartxis\Shop\Http\Controllers\Account\WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/{product}', [Cartxis\Shop\Http\Controllers\Account\WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::delete('/wishlist/{product}', [Cartxis\Shop\Http\Controllers\Account\WishlistController::class, 'destroy'])->name('wishlist.destroy');
        Route::get('/profile', [Cartxis\Shop\Http\Controllers\Account\ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [Cartxis\Shop\Http\Controllers\Account\ProfileController::class, 'update'])->name('profile.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes
    |--------------------------------------------------------------------------
    */
    Route::group([
        'prefix' => 'checkout',
        'as' => 'shop.checkout.'
    ], function () {
        Route::get('/', [Cartxis\Shop\Http\Controllers\Checkout\CheckoutController::class, 'index'])->name('index');
        Route::post('/', [Cartxis\Shop\Http\Controllers\Checkout\CheckoutController::class, 'store'])->name('store');
        Route::post('/shipping', [Cartxis\Shop\Http\Controllers\Checkout\CheckoutController::class, 'updateShipping'])->name('shipping.update');
        Route::get('/success/{order}', [Cartxis\Shop\Http\Controllers\Checkout\CheckoutController::class, 'success'])->name('success');
    });
});
