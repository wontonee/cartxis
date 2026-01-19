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
