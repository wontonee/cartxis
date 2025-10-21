<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Vortex\Admin\Http\Controllers\Auth\AdminLoginController;
use Packages\Core\Http\Controllers\Admin\ThemeController;
use Vortex\Core\Http\Controllers\Admin\Settings\GeneralSettingsController;

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
        });
    });
});

