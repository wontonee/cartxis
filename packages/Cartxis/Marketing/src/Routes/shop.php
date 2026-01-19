<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Marketing\Http\Controllers\CouponController;

/*
|--------------------------------------------------------------------------
| Marketing Shop Routes (Frontend API)
|--------------------------------------------------------------------------
|
| Public-facing routes for coupon application and validation.
|
*/

Route::middleware(['web'])->prefix('shop/coupons')->name('shop.coupons.')->group(function () {
    
    // Apply coupon to cart
    Route::post('/apply', [CouponController::class, 'apply'])->name('apply');
    
    // Remove coupon from cart
    Route::delete('/remove', [CouponController::class, 'remove'])->name('remove');
    
    // Validate coupon (check eligibility without applying)
    Route::post('/validate', [CouponController::class, 'validate'])->name('validate');
    
    // Get list of available public coupons
    Route::get('/available', [CouponController::class, 'listPublic'])->name('available');
});
