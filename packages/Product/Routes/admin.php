<?php

use Vortex\Product\Http\Controllers\Admin\ProductController;
use Vortex\Product\Http\Controllers\Admin\ProductImageController;
use Vortex\Product\Http\Controllers\Admin\CategoryController;
use Vortex\Product\Http\Controllers\Admin\AttributeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:admin'])
    ->prefix('admin/catalog')
    ->name('admin.catalog.')
    ->group(function () {
        // Product Management
        Route::resource('products', ProductController::class);
        Route::post('products/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
        Route::post('products/bulk-status', [ProductController::class, 'bulkUpdateStatus'])->name('products.bulk-status');
        
        // Product Image Management
        Route::post('products/{product}/images/upload', [ProductImageController::class, 'upload'])->name('products.images.upload');
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
        Route::post('products/{product}/images/{image}/set-main', [ProductImageController::class, 'setMain'])->name('products.images.set-main');
        Route::put('products/{product}/images/{image}', [ProductImageController::class, 'update'])->name('products.images.update');

        // Category Management
        Route::resource('categories', CategoryController::class);
        Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
        Route::post('categories/bulk-status', [CategoryController::class, 'bulkUpdateStatus'])->name('categories.bulk-status');

        // Attribute Management
        Route::resource('attributes', AttributeController::class);
        Route::post('attributes/bulk-destroy', [AttributeController::class, 'bulkDestroy'])->name('attributes.bulk-destroy');
    });
