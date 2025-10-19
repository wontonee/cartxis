<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Core Application Routes
|--------------------------------------------------------------------------
|
| Package-specific routes are loaded by their respective service providers:
| - Product routes: packages/Product/Routes/web.php (loaded by ProductServiceProvider)
| - Cart API routes: packages/Cart/Routes/api.php (loaded by CartServiceProvider)
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
