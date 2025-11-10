<?php

use Illuminate\Support\Facades\Route;
use Vortex\System\Http\Controllers\CacheController;
use Vortex\System\Http\Controllers\MaintenanceController;
use Vortex\System\Http\Controllers\Admin\MenuController;

Route::middleware(['web', 'auth:admin'])
    ->prefix('admin/system')
    ->name('admin.system.')
    ->group(function () {
        
        // Cache Management Routes
        Route::prefix('cache')->name('cache.')->group(function () {
            Route::get('/', [CacheController::class, 'index'])->name('index');
            Route::get('/statistics', [CacheController::class, 'statistics'])->name('statistics');
            Route::post('/clear', [CacheController::class, 'clear'])->name('clear');
            Route::post('/rebuild', [CacheController::class, 'rebuild'])->name('rebuild');
        });

        // Maintenance Mode Routes
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'index'])->name('index');
            Route::get('/status', [MaintenanceController::class, 'status'])->name('status');
            Route::post('/enable', [MaintenanceController::class, 'enable'])->name('enable');
            Route::post('/disable', [MaintenanceController::class, 'disable'])->name('disable');
            Route::post('/schedule', [MaintenanceController::class, 'schedule'])->name('schedule');
            Route::put('/settings', [MaintenanceController::class, 'updateSettings'])->name('updateSettings');
            Route::post('/regenerate-secret', [MaintenanceController::class, 'regenerateSecret'])->name('regenerateSecret');
            Route::get('/history', [MaintenanceController::class, 'history'])->name('history');
        });

        // Menu Management Routes
        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::post('/', [MenuController::class, 'store'])->name('store');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [MenuController::class, 'reorder'])->name('reorder');
            Route::post('/{menu}/toggle', [MenuController::class, 'toggle'])->name('toggle');
        });
    });
