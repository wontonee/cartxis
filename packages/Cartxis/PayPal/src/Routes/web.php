<?php

use Illuminate\Support\Facades\Route;
use Cartxis\PayPal\Http\Controllers\PayPalController;

/*
|--------------------------------------------------------------------------
| PayPal Gateway Routes
|--------------------------------------------------------------------------
|
| Routes for PayPal payment gateway callbacks and webhooks.
|
*/

// Payment callback (after customer returns from PayPal)
Route::get('/paypal/callback', [PayPalController::class, 'callback'])->name('paypal.callback');

// Webhook handler (for PayPal IPNs)
Route::post('/paypal/webhook', [PayPalController::class, 'webhook'])->name('paypal.webhook');
