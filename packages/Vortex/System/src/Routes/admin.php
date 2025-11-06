<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vortex\System\Http\Controllers\Admin\MenuController;

// System Admin Routes
Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // System Management
    Route::prefix('system')->name('system.')->group(function () {
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
