<?php

use Cartxis\Product\Http\Controllers\Admin\ProductController;
use Cartxis\Product\Http\Controllers\Admin\ProductImageController;
use Cartxis\Product\Http\Controllers\Admin\ProductDownloadController;
use Cartxis\Product\Http\Controllers\Admin\CategoryController;
use Cartxis\Product\Http\Controllers\Admin\AttributeController;
use Cartxis\Product\Http\Controllers\Admin\BrandController;
use Cartxis\Product\Http\Controllers\Admin\ReviewController;
use Cartxis\Product\Http\Controllers\Admin\ProductAiController;
use Cartxis\Product\Http\Controllers\Admin\QuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:admin'])
    ->prefix('admin/catalog')
    ->name('admin.catalog.')
    ->group(function () {
        // Product Management
        Route::resource('products', ProductController::class);
        Route::post('products/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
        Route::post('products/bulk-status', [ProductController::class, 'bulkUpdateStatus'])->name('products.bulk-status');
        Route::post('products/{product}/generate-description', [ProductAiController::class, 'generateDescription'])->name('products.generate-description');
        Route::post('products/price-comparison', [ProductAiController::class, 'generatePriceComparison'])->name('products.price-comparison');

        // Product Image Management
        Route::post('products/{product}/images/upload', [ProductImageController::class, 'upload'])->name('products.images.upload');
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
        Route::post('products/{product}/images/{image}/set-main', [ProductImageController::class, 'setMain'])->name('products.images.set-main');
        Route::put('products/{product}/images/{image}', [ProductImageController::class, 'update'])->name('products.images.update');

        // Product Download Management
        Route::post('products/{product}/downloads', [ProductDownloadController::class, 'store'])->name('products.downloads.store');
        Route::put('products/{product}/downloads/{download}', [ProductDownloadController::class, 'update'])->name('products.downloads.update');
        Route::delete('products/{product}/downloads/{download}', [ProductDownloadController::class, 'destroy'])->name('products.downloads.destroy');

        // Quote Requests
        Route::get('quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
        Route::get('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
        Route::put('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'update'])->name('quote-requests.update');
        Route::delete('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'destroy'])->name('quote-requests.destroy');

        // Category Management
        Route::resource('categories', CategoryController::class);
        Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
        Route::post('categories/bulk-status', [CategoryController::class, 'bulkUpdateStatus'])->name('categories.bulk-status');

        // Attribute Management
        Route::resource('attributes', AttributeController::class);
        Route::post('attributes/bulk-destroy', [AttributeController::class, 'bulkDestroy'])->name('attributes.bulk-destroy');

        // Brand Management
        Route::resource('brands', BrandController::class);
        Route::post('brands/bulk-destroy', [BrandController::class, 'bulkDestroy'])->name('brands.bulk-destroy');
        Route::post('brands/bulk-status', [BrandController::class, 'bulkStatus'])->name('brands.bulk-status');

        // Review Management
        Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'destroy']);
        Route::post('reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.update-status');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('reviews/bulk-action', [ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
    });
