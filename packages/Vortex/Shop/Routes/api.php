<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all API routes for the Shop package.
| These routes are loaded by the ShopServiceProvider.
|
*/

Route::group([
    'prefix' => 'api/shop',
    'middleware' => ['api'],
    'as' => 'api.shop.'
], function () {

    /*
    |--------------------------------------------------------------------------
    | Product API Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/products', [Vortex\Shop\Http\Controllers\Api\ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{id}', [Vortex\Shop\Http\Controllers\Api\ProductController::class, 'show'])->name('products.show');

    /*
    |--------------------------------------------------------------------------
    | Category API Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/categories', [Vortex\Shop\Http\Controllers\Api\CategoryController::class, 'index'])->name('categories.index');
    // Route::get('/categories/{id}', [Vortex\Shop\Http\Controllers\Api\CategoryController::class, 'show'])->name('categories.show');

    /*
    |--------------------------------------------------------------------------
    | Search API Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/search', [Vortex\Shop\Http\Controllers\Api\SearchController::class, 'index'])->name('search');
    // Route::get('/search/suggestions', [Vortex\Shop\Http\Controllers\Api\SearchController::class, 'suggestions'])->name('search.suggestions');

    /*
    |--------------------------------------------------------------------------
    | Wishlist API Routes
    |--------------------------------------------------------------------------
    */
    // Route::middleware(['auth:sanctum'])->group(function () {
    //     Route::get('/wishlist', [Vortex\Shop\Http\Controllers\Api\WishlistController::class, 'index'])->name('wishlist.index');
    //     Route::post('/wishlist', [Vortex\Shop\Http\Controllers\Api\WishlistController::class, 'store'])->name('wishlist.store');
    //     Route::delete('/wishlist/{id}', [Vortex\Shop\Http\Controllers\Api\WishlistController::class, 'destroy'])->name('wishlist.destroy');
    // });
});
