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
    Route::get('/', [Vortex\Shop\Http\Controllers\HomeController::class, 'index'])->name('shop.home');

    /*
    |--------------------------------------------------------------------------
    | Product Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/products', [Vortex\Shop\Http\Controllers\ProductController::class, 'index'])->name('shop.products.index');
    Route::get('/products/{slug}/quick-view', [Vortex\Shop\Http\Controllers\ProductController::class, 'quickView'])->name('shop.products.quick-view');
    Route::get('/product/{slug}', [Vortex\Shop\Http\Controllers\ProductController::class, 'show'])->name('shop.products.show');

    /*
    |--------------------------------------------------------------------------
    | Category Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/category/{slug}', [Vortex\Shop\Http\Controllers\CategoryController::class, 'show'])->name('shop.categories.show');

    /*
    |--------------------------------------------------------------------------
    | Search Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/search', [Vortex\Shop\Http\Controllers\SearchController::class, 'index'])->name('shop.search');

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
        Route::get('/', [Vortex\Shop\Http\Controllers\Account\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [Vortex\Shop\Http\Controllers\Account\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [Vortex\Shop\Http\Controllers\Account\OrderController::class, 'show'])->name('orders.show');
        // Route::get('/profile', [Vortex\Shop\Http\Controllers\Account\ProfileController::class, 'edit'])->name('profile.edit');
        // Route::put('/profile', [Vortex\Shop\Http\Controllers\Account\ProfileController::class, 'update'])->name('profile.update');
        // Route::get('/addresses', [Vortex\Shop\Http\Controllers\Account\AddressController::class, 'index'])->name('addresses.index');
        // Route::get('/wishlist', [Vortex\Shop\Http\Controllers\Account\WishlistController::class, 'index'])->name('wishlist.index');
        // Route::get('/reviews', [Vortex\Shop\Http\Controllers\Account\ReviewController::class, 'index'])->name('reviews.index');
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
        Route::get('/', [Vortex\Shop\Http\Controllers\Checkout\CheckoutController::class, 'index'])->name('index');
        Route::post('/', [Vortex\Shop\Http\Controllers\Checkout\CheckoutController::class, 'store'])->name('store');
        Route::post('/shipping', [Vortex\Shop\Http\Controllers\Checkout\CheckoutController::class, 'updateShipping'])->name('shipping.update');
        Route::get('/success/{order}', [Vortex\Shop\Http\Controllers\Checkout\CheckoutController::class, 'success'])->name('success');
    });
});
