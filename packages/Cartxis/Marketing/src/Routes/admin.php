<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Marketing\Http\Controllers\CouponController;
use Cartxis\Marketing\Http\Controllers\PromotionController;

/*
|--------------------------------------------------------------------------
| Marketing Admin Routes
|--------------------------------------------------------------------------
|
| Admin routes for managing coupons, promotions, and marketing campaigns.
| All routes require authentication and admin middleware.
|
*/

Route::middleware(['web', 'auth:admin'])->prefix('admin/marketing')->name('admin.marketing.')->group(function () {
    
    // Coupons
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('/{coupon}', [CouponController::class, 'show'])->name('show');
        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::put('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
        
        // Bulk actions
        Route::post('/bulk-activate', [CouponController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('/bulk-deactivate', [CouponController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::post('/bulk-delete', [CouponController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Analytics
        Route::get('/{coupon}/analytics', [CouponController::class, 'analytics'])->name('analytics');
    });
    
    // Promotions
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index');
        Route::get('/create', [PromotionController::class, 'create'])->name('create');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::get('/{promotion}', [PromotionController::class, 'show'])->name('show');
        Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])->name('edit');
        Route::put('/{promotion}', [PromotionController::class, 'update'])->name('update');
        Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->name('destroy');
        
        // Bulk actions
        Route::post('/bulk-activate', [PromotionController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('/bulk-deactivate', [PromotionController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::post('/bulk-delete', [PromotionController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Analytics
        Route::get('/{promotion}/analytics', [PromotionController::class, 'analytics'])->name('analytics');
    });
});
