<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Vortex\Admin\Http\Controllers\Auth\AdminLoginController;
use Vortex\Core\Http\Controllers\Admin\ThemeController;
use Vortex\Core\Http\Controllers\Admin\Settings\GeneralSettingsController;
use Vortex\Settings\Http\Controllers\Admin\ShippingMethodsController;
use Vortex\Settings\Http\Controllers\Admin\ChannelsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All admin panel routes are defined here.
| These routes are loaded by AdminServiceProvider.
|
*/

Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    // Admin Authentication Routes (Guest)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    // Admin Authenticated Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        
        // Dashboard
        Route::get('/dashboard', function () {
            return inertia('Admin/Dashboard/Index', [
                'auth' => [
                    'user' => Auth::guard('admin')->user()
                ]
            ]);
        })->name('dashboard');
        
        // Appearance -> Themes
        Route::prefix('appearance/themes')->name('themes.')->group(function () {
            Route::get('/', [ThemeController::class, 'index'])->name('index');
            Route::post('/{slug}/activate', [ThemeController::class, 'activate'])->name('activate');
            Route::get('/{slug}/settings', [ThemeController::class, 'settings'])->name('settings');
            Route::put('/{slug}/settings', [ThemeController::class, 'updateSettings'])->name('settings.update');
            Route::delete('/{slug}', [ThemeController::class, 'destroy'])->name('destroy');
            Route::post('/upload', [ThemeController::class, 'upload'])->name('upload');
        });

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            // General Settings
            Route::prefix('general')->name('general.')->group(function () {
                Route::get('/', function () {
                    return inertia('Admin/Settings/General/Index', [
                        'settings' => [],
                        'cacheStatus' => [
                            'config_cached' => app()->configurationIsCached(),
                            'routes_cached' => app()->routesAreCached(),
                            'events_cached' => app()->eventsAreCached(),
                        ]
                    ]);
                })->name('index');
            });

            // Channels Routes
            Route::resource('channels', ChannelsController::class);
            Route::post('channels/{channel}/update-theme', [ChannelsController::class, 'updateTheme'])->name('channels.updateTheme');
            Route::post('channels/{channel}/set-default', [ChannelsController::class, 'setDefault'])->name('channels.setDefault');
            Route::post('channels/{channel}/toggle-status', [ChannelsController::class, 'toggleStatus'])->name('channels.toggleStatus');

            // Shipping Methods Routes
            Route::resource('shipping-methods', ShippingMethodsController::class);
            Route::post('shipping-methods/{shippingMethod}/set-default', [ShippingMethodsController::class, 'setDefault'])->name('shipping-methods.setDefault');
            Route::post('shipping-methods/{shippingMethod}/toggle-status', [ShippingMethodsController::class, 'toggleStatus'])->name('shipping-methods.toggleStatus');
            Route::post('shipping-methods/{shippingMethod}/add-rate', [ShippingMethodsController::class, 'addRate'])->name('shipping-methods.addRate');
            Route::put('shipping-rates/{shippingRate}', [ShippingMethodsController::class, 'updateRate'])->name('shipping-rates.update');
            Route::delete('shipping-rates/{shippingRate}', [ShippingMethodsController::class, 'deleteRate'])->name('shipping-rates.delete');
            Route::post('shipping-rates/{shippingRate}/toggle-status', [ShippingMethodsController::class, 'toggleRateStatus'])->name('shipping-rates.toggleStatus');
            Route::post('shipping-methods/{shippingMethod}/calculate-cost', [ShippingMethodsController::class, 'calculateCost'])->name('shipping-methods.calculateCost');
        });
    });
});

