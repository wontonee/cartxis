<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Cartxis\Admin\Http\Controllers\Auth\AdminLoginController;
use Cartxis\Admin\Http\Controllers\UserController;
use Cartxis\Admin\Http\Controllers\ProfileController;
use Cartxis\Admin\Http\Controllers\PasswordController;
use Cartxis\Admin\Http\Controllers\NotificationController;
use Cartxis\Admin\Http\Controllers\ActivityLogController;
use Cartxis\Core\Http\Controllers\Admin\DashboardController;
use Cartxis\Core\Http\Controllers\Admin\ThemeController;
use Cartxis\Core\Http\Controllers\Admin\Settings\GeneralSettingsController;

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
    Route::middleware(\Cartxis\Admin\Http\Middleware\RedirectIfAdminAuthenticated::class)->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    // Admin Authenticated Routes
    Route::middleware(['auth:admin', 'prevent.user.admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        });

        // Activity logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data');
        
        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::put('/{user}/change-password', [UserController::class, 'changePassword'])->name('change-password');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });
        
        // Admin Profile Management
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::post('/', [ProfileController::class, 'update'])->name('update');
        });
        
        // Admin Password Management
        Route::prefix('password')->name('password.')->group(function () {
            Route::get('/', [PasswordController::class, 'edit'])->name('edit');
            Route::post('/', [PasswordController::class, 'update'])->name('update');
        });
        
        // Appearance -> Themes
        Route::get('appearance', [ThemeController::class, 'appearance'])->name('appearance.index');
        Route::prefix('appearance/themes')->name('themes.')->group(function () {
            Route::get('/', [ThemeController::class, 'index'])->name('index');
            Route::get('/active-settings', [ThemeController::class, 'activeSettings'])->name('active-settings');
            Route::post('/{slug}/activate', [ThemeController::class, 'activate'])->name('activate');
            Route::get('/{slug}/settings', [ThemeController::class, 'settings'])->name('settings');
            Route::put('/{slug}/settings', [ThemeController::class, 'updateSettings'])->name('settings.update');
            Route::delete('/{slug}', [ThemeController::class, 'destroy'])->name('destroy');
            Route::post('/{slug}/import-data', [ThemeController::class, 'importData'])->name('import-data');
            Route::post('/{slug}/screenshot', [ThemeController::class, 'uploadScreenshot'])->name('screenshot');
            Route::post('/upload', [ThemeController::class, 'upload'])->name('upload');
        });
        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
        });
    });
});

