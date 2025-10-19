<?php

use Illuminate\Support\Facades\Route;
use Vortex\Product\Http\Controllers\FrontEnd\ProductController;

/*
|--------------------------------------------------------------------------
| Product Frontend Routes
|--------------------------------------------------------------------------
|
| These routes handle public-facing product pages including
| product listing, filtering, sorting, and product detail pages.
|
*/

Route::middleware('web')->prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{slug}/quick-view', [ProductController::class, 'quickView'])->name('quick-view');
    Route::get('/{slug}', [ProductController::class, 'show'])->name('show');
});
