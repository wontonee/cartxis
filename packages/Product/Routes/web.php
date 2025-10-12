<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Public product routes will go here
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::get('/products/{slug}', [ProductController::class, 'show']);
});
