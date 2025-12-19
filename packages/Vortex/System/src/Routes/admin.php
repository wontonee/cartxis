<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vortex\System\Http\Controllers\Admin\MenuController;
use Vortex\System\Http\Controllers\Admin\ExtensionsController;

// System Admin Routes
Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // System Management
    Route::prefix('system')->name('system.')->group(function () {
        // Extensions Management
        Route::prefix('extensions')->name('extensions.')->group(function () {
            Route::get('/', [ExtensionsController::class, 'index'])->name('index');
            Route::post('/sync', [ExtensionsController::class, 'sync'])->name('sync');
            Route::post('/{code}/install', [ExtensionsController::class, 'install'])->name('install');
            Route::post('/{code}/activate', [ExtensionsController::class, 'activate'])->name('activate');
            Route::post('/{code}/deactivate', [ExtensionsController::class, 'deactivate'])->name('deactivate');
            Route::delete('/{code}', [ExtensionsController::class, 'uninstall'])->name('uninstall');
        });

        // Menu Management
        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::post('/', [MenuController::class, 'store'])->name('store');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [MenuController::class, 'reorder'])->name('reorder');
            Route::post('/{menu}/toggle', [MenuController::class, 'toggle'])->name('toggle');
        });
    });
});
