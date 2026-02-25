<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Cart\Http\Controllers\Api\CartController;

/*
|--------------------------------------------------------------------------
| Cart API Routes
|--------------------------------------------------------------------------
|
| These routes handle cart operations via AJAX calls.
| Session-based for guests, database-ready for authenticated users.
|
*/

Route::middleware(['web'])->prefix('api/cart')->name('api.cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{itemId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{itemId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});
