<?php

use Illuminate\Support\Facades\Route;
use Vortex\Razorpay\Http\Controllers\RazorpayController;

// Webhook route (public, no auth required)
Route::post('webhooks/razorpay', [RazorpayController::class, 'webhook'])
    ->name('razorpay.webhook');

// Note: Razorpay configuration is handled by PaymentMethodsController
// at admin/settings/payment-methods/{type}/configure
