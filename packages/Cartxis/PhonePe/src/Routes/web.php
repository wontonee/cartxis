<?php

use Illuminate\Support\Facades\Route;
use Cartxis\PhonePe\Http\Controllers\PhonePeController;

/*
|--------------------------------------------------------------------------
| PhonePe Payment Routes
|--------------------------------------------------------------------------
|
| Routes for handling PhonePe payment callbacks and webhooks.
|
*/

Route::middleware(['web'])->group(function () {
    // Payment success callback (redirect from PhonePe after successful payment)
    Route::get('phonepe/success/{order}', [PhonePeController::class, 'paymentSuccess'])
        ->name('phonepe.success');

    // Payment cancel callback (redirect from PhonePe when user cancels)
    Route::get('phonepe/cancel/{order}', [PhonePeController::class, 'paymentCancel'])
        ->name('phonepe.cancel');
});

// Webhook route (no CSRF protection needed for webhooks)
Route::post('phonepe/webhook', [PhonePeController::class, 'webhook'])
    ->name('phonepe.webhook')
    ->withoutMiddleware(['web', 'csrf']);
