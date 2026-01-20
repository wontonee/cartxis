<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Cartxis\CMS\Http\Controllers\PageController;
use Cartxis\CMS\Http\Controllers\MenuController;

// Frontend CMS Routes
Route::middleware('web')->group(function () {
    // Menu API endpoints
    Route::prefix('api/menus')->group(function () {
        Route::get('/all', [MenuController::class, 'getAllMenus'])->name('api.menus.all');
        Route::get('/{type}', [MenuController::class, 'getMenu'])->name('api.menus.get')
            ->where('type', 'header|footer|mobile');
    });

    // Display page by slug
    // Exclude auth routes and other system routes
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('page.show')
        ->where('slug', '^(?!register|login|logout|forgot-password|reset-password|verify-email|dashboard|settings|admin|api)[a-z0-9-]+$');
});
