<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Cartxis\Blog\Http\Controllers\Admin\BlogPostsController;
use Cartxis\Blog\Http\Controllers\Admin\BlogCategoriesController;

Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('blog/categories')->name('blog.categories.')->group(function () {
        Route::get('/', [BlogCategoriesController::class, 'index'])->name('index');
        Route::get('/create', [BlogCategoriesController::class, 'create'])->name('create');
        Route::post('/', [BlogCategoriesController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [BlogCategoriesController::class, 'edit'])->name('edit');
        Route::put('/{category}', [BlogCategoriesController::class, 'update'])->name('update');
        Route::delete('/{category}', [BlogCategoriesController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogPostsController::class, 'index'])->name('index');
        Route::get('/create', [BlogPostsController::class, 'create'])->name('create');
        Route::post('/', [BlogPostsController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [BlogPostsController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BlogPostsController::class, 'update'])->name('update');
        Route::delete('/{post}', [BlogPostsController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [BlogPostsController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/check-slug', [BlogPostsController::class, 'checkSlug'])->name('check-slug');
    });
});
