<?php

use Illuminate\Support\Facades\Route;
use Cartxis\PayUMoney\Http\Controllers\PayUMoneyController;

/*
|--------------------------------------------------------------------------
| PayUMoney Gateway Routes
|--------------------------------------------------------------------------
|
| Routes for PayUMoney payment gateway callbacks.
|
*/

// Payment callback (success, failure, and cancel)
Route::post('/payumoney/callback', [PayUMoneyController::class, 'callback'])->name('payumoney.callback');

// IPN webhook (server-to-server notification from PayUMoney — no CSRF token)
Route::post('/payumoney/webhook', [PayUMoneyController::class, 'webhook'])
    ->name('payumoney.webhook')
    ->withoutMiddleware(['web', 'csrf']);
