<?php

use Illuminate\Support\Facades\Route;
use Vortex\Cart\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Cart Web Routes
|--------------------------------------------------------------------------
|
| These routes handle cart page display for customers.
|
*/

Route::middleware(['web'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
});
