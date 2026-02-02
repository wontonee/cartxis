<?php

use Illuminate\Support\Facades\Route;
use Cartxis\API\Http\Controllers\V1\AuthController;
use Cartxis\API\Http\Controllers\V1\ProductController;
use Cartxis\API\Http\Controllers\V1\CategoryController;
use Cartxis\API\Http\Controllers\V1\CartController;
use Cartxis\API\Http\Controllers\V1\BannerController;
use Cartxis\API\Http\Controllers\V1\CheckoutController;
use Cartxis\API\Http\Controllers\V1\CustomerController;
use Cartxis\API\Http\Controllers\V1\OrderController;
use Cartxis\API\Http\Controllers\V1\WishlistController;
use Cartxis\API\Http\Controllers\V1\ReviewController;
use Cartxis\API\Http\Controllers\V1\SearchController;
use Cartxis\API\Http\Controllers\V1\CurrencyController;
use Cartxis\API\Http\Controllers\V1\ApiSyncController;
use Cartxis\API\Http\Controllers\V1\ProductAiController;
use Cartxis\API\Http\Middleware\TrackApiSync;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Here are the API routes for the Cartxis mobile application.
| These routes use Laravel Sanctum for authentication.
| Base path: /api/v1
|
*/

Route::prefix('api/v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    });

    // Products (Public browsing)
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/featured', [ProductController::class, 'featured']);
        Route::get('/on-sale', [ProductController::class, 'onSale']);
        Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::get('/{id}/related', [ProductController::class, 'related']);
        Route::get('/{id}/reviews', [ReviewController::class, 'productReviews']);
    });

    // Home/mobile banners (CMS blocks of type=banner)
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('banners/{identifier}', [BannerController::class, 'show']);

    // Categories (Public browsing)
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::get('/{id}/products', [CategoryController::class, 'products']);
    });

    // Currency (Public)
    Route::prefix('currency')->group(function () {
        Route::get('/default', [CurrencyController::class, 'default']);
        Route::get('/', [CurrencyController::class, 'index']);
    });

    // Search (Public)
    Route::prefix('search')->group(function () {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth:sanctum', 'throttle:api', TrackApiSync::class])->group(function () {
        
        // Authentication (Authenticated)
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/password/change', [AuthController::class, 'changePassword']);
            Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
            Route::post('/refresh', [AuthController::class, 'refreshToken']);
        });

        // Cart Management
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/items/{id}', [CartController::class, 'update']);
            Route::delete('/items/{id}', [CartController::class, 'remove']);
            Route::delete('/clear', [CartController::class, 'clear']);
            Route::post('/apply-coupon', [CartController::class, 'applyCoupon']);
            Route::delete('/remove-coupon', [CartController::class, 'removeCoupon']);
            Route::get('/count', [CartController::class, 'count']);
        });

        // Checkout Process
        Route::prefix('checkout')->group(function () {
            Route::get('/init', [CheckoutController::class, 'init']);
            Route::post('/shipping-address', [CheckoutController::class, 'setShippingAddress']);
            Route::post('/billing-address', [CheckoutController::class, 'setBillingAddress']);
            Route::get('/shipping-methods', [CheckoutController::class, 'getShippingMethods']);
            Route::post('/shipping-method', [CheckoutController::class, 'setShippingMethod']);
            Route::get('/payment-methods', [CheckoutController::class, 'getPaymentMethods']);
            Route::post('/payment-method', [CheckoutController::class, 'setPaymentMethod']);
            Route::get('/summary', [CheckoutController::class, 'summary']);
            Route::post('/place-order', [CheckoutController::class, 'placeOrder']);
        });

        // Customer Account
        Route::prefix('customer')->group(function () {
            // Profile
            Route::get('/profile', [CustomerController::class, 'profile']);
            Route::put('/profile', [CustomerController::class, 'updateProfile']);
            
            // Orders
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
            Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);
            Route::get('/orders/{id}/track', [OrderController::class, 'track']);
            
            // Addresses
            Route::get('/addresses', [CustomerController::class, 'addresses']);
            Route::post('/addresses', [CustomerController::class, 'storeAddress']);
            Route::put('/addresses/{id}', [CustomerController::class, 'updateAddress']); // Use this to set default: {"is_default_shipping": true}
            Route::delete('/addresses/{id}', [CustomerController::class, 'deleteAddress']);
            
            // Wishlist
            Route::get('/wishlist', [WishlistController::class, 'index']);
            Route::post('/wishlist/add', [WishlistController::class, 'add']);
            Route::delete('/wishlist/{id}', [WishlistController::class, 'remove']);
            Route::post('/wishlist/{id}/move-to-cart', [WishlistController::class, 'moveToCart']);
        });

        // Reviews
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('/{id}', [ReviewController::class, 'update']);
            Route::delete('/{id}', [ReviewController::class, 'destroy']);
            Route::post('/{id}/vote', [ReviewController::class, 'vote']);
        });

        // AI Product Description Generator
        Route::post('products/{id}/generate-description', [ProductAiController::class, 'generateDescription']);

        // API Sync (Mobile connectivity)
        Route::prefix('system/api-sync')->group(function () {
            Route::get('/status', [ApiSyncController::class, 'status']);
            Route::post('/heartbeat', [ApiSyncController::class, 'heartbeat']);
        });
    });
});
