<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Cartxis\Blog\Http\Controllers\BlogController;

Route::middleware(['web'])->group(function () {
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
});
