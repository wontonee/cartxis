<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vortex\CMS\Http\Controllers\PageController;

// Frontend CMS Routes
Route::middleware('web')->group(function () {
    // Display page by slug
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('page.show')
        ->where('slug', '[a-z0-9-]+');
});
