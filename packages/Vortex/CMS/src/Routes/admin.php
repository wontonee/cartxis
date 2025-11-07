<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vortex\CMS\Http\Controllers\Admin\PagesController;
use Vortex\CMS\Http\Controllers\Admin\MediaController;
use Vortex\CMS\Http\Controllers\Admin\FolderController;
use Vortex\CMS\Http\Controllers\Admin\StorefrontMenuController;
use Vortex\CMS\Http\Controllers\Admin\BlocksController;

// CMS Admin Routes
Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('content')->name('content.')->group(function () {
        // Pages Management
        Route::prefix('pages')->name('pages.')->group(function () {
            Route::get('/', [PagesController::class, 'index'])->name('index');
            Route::get('/create', [PagesController::class, 'create'])->name('create');
            Route::post('/', [PagesController::class, 'store'])->name('store');
            Route::get('/{page}/edit', [PagesController::class, 'edit'])->name('edit');
            Route::put('/{page}', [PagesController::class, 'update'])->name('update');
            Route::delete('/{page}', [PagesController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [PagesController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{page}/preview', [PagesController::class, 'preview'])->name('preview');
            Route::post('/check-slug', [PagesController::class, 'checkSlug'])->name('check-slug');
        });

        // Media Library
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
            Route::get('/{media}', [MediaController::class, 'show'])->name('show');
            Route::put('/{media}', [MediaController::class, 'update'])->name('update');
            Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [MediaController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/picker', [MediaController::class, 'picker'])->name('picker');
        });

        // Media Folders
        Route::prefix('folders')->name('folders.')->group(function () {
            Route::post('/', [FolderController::class, 'store'])->name('store');
            Route::put('/{folder}', [FolderController::class, 'update'])->name('update');
            Route::delete('/{folder}', [FolderController::class, 'destroy'])->name('destroy');
        });

        // Blocks Management
        Route::prefix('blocks')->name('blocks.')->group(function () {
            Route::get('/', [BlocksController::class, 'index'])->name('index');
            Route::get('/create', [BlocksController::class, 'create'])->name('create');
            Route::post('/', [BlocksController::class, 'store'])->name('store');
            Route::get('/{block}/edit', [BlocksController::class, 'edit'])->name('edit');
            Route::put('/{block}', [BlocksController::class, 'update'])->name('update');
            Route::delete('/{block}', [BlocksController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [BlocksController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/check-identifier', [BlocksController::class, 'checkIdentifier'])->name('check-identifier');
            Route::post('/generate-identifier', [BlocksController::class, 'generateIdentifier'])->name('generate-identifier');
        });

        // Storefront Menus
        Route::prefix('storefront-menus')->name('storefront-menus.')->group(function () {
            Route::get('/', [StorefrontMenuController::class, 'index'])->name('index');
            Route::post('/', [StorefrontMenuController::class, 'store'])->name('store');
            Route::put('/{menuItem}', [StorefrontMenuController::class, 'update'])->name('update');
            Route::delete('/{menuItem}', [StorefrontMenuController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [StorefrontMenuController::class, 'reorder'])->name('reorder');
            Route::post('/{menuItem}/toggle', [StorefrontMenuController::class, 'toggle'])->name('toggle');
        });
    });
});
