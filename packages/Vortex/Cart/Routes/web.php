<?php

use Illuminate\Support\Facades\Route;
use Vortex\Cart\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Cart Web Routes
|--------------------------------------------------------------------------
|
| These routes handle cart page display and actions for customers.
|
*/

Route::middleware(['web'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{index}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{index}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});
