<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Stripe\Http\Controllers\StripeController;

// Stripe payment routes
Route::middleware(['web'])->group(function () {
    // Payment success callback (from Stripe Checkout)
    Route::get('stripe/success/{order}', [StripeController::class, 'paymentSuccess'])
        ->name('stripe.success');
    
    // Payment cancel callback (from Stripe Checkout)
    Route::get('stripe/cancel/{order}', [StripeController::class, 'paymentCancel'])
        ->name('stripe.cancel');
    
    // Create payment intent (legacy API - optional)
    Route::post('stripe/create-payment-intent', [StripeController::class, 'createPaymentIntent'])
        ->name('stripe.create-payment-intent');
});
