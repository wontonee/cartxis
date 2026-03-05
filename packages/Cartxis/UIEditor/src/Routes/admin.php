<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Cartxis\UIEditor\Http\Controllers\Admin\EditorController;
use Cartxis\UIEditor\Http\Controllers\Admin\HomepageEditorController;
use Cartxis\UIEditor\Http\Controllers\Admin\ProductsSearchController;
use Cartxis\UIEditor\Http\Controllers\Admin\SavedBlocksController;
use Cartxis\UIEditor\Http\Controllers\Admin\GlobalRegionController;

Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('uieditor')->name('uieditor.')->group(function () {

        // ── CMS Page Editor ───────────────────────────────────────────────────
        Route::prefix('pages/{page}')->name('pages.')->group(function () {
            Route::get('/editor',      [EditorController::class, 'show'])->name('editor');
            Route::post('/save',       [EditorController::class, 'save'])->name('save');
            Route::post('/publish',    [EditorController::class, 'publish'])->name('publish');
            Route::post('/unpublish',  [EditorController::class, 'unpublish'])->name('unpublish');
            Route::get('/preview',     [EditorController::class, 'preview'])->name('preview');
            Route::put('/settings',    [EditorController::class, 'updatePageSettings'])->name('settings');
        });

        // ── Homepage Editor ───────────────────────────────────────────────────
        // The homepage is now just another page in the Pages list (is_homepage = true).
        // The GET /homepage/editor URL redirects to the pages list for bookmarks.
        // The save/publish/unpublish POST routes are kept because EditorController delegates here.
        Route::prefix('homepage')->name('homepage.')->group(function () {
            Route::get('/editor', function () {
                return redirect('/admin/content/pages');
            })->name('editor');
            Route::post('/save',     [HomepageEditorController::class, 'save'])->name('save');
            Route::post('/publish',  [HomepageEditorController::class, 'publish'])->name('publish');
            Route::post('/unpublish',[HomepageEditorController::class, 'unpublish'])->name('unpublish');
            Route::get('/preview',   [HomepageEditorController::class, 'preview'])->name('preview');
        });

        // ── Products Search (for Products Grid block panel) ───────────────────
        Route::get('/products/search', [ProductsSearchController::class, 'index'])
            ->name('products.search');

        // ── Built-in Snippets (seeded, read-only) ────────────────────────────
        Route::get('snippets', [SavedBlocksController::class, 'snippets'])->name('snippets');

        // ── Saved Blocks (user-created reusables) ─────────────────────────────
        Route::prefix('saved-blocks')->name('saved-blocks.')->group(function () {
            Route::get('/',        [SavedBlocksController::class, 'index'])  ->name('index');
            Route::post('/',       [SavedBlocksController::class, 'store'])  ->name('store');
            Route::delete('/{id}', [SavedBlocksController::class, 'destroy'])->name('destroy');
        });
        // ── Global Regions (Reusable layout regions) ──────────────────────────
        Route::prefix('regions')->name('regions.')->group(function () {
            Route::get('/',                         [GlobalRegionController::class, 'index'])       ->name('index');
            Route::post('/',                        [GlobalRegionController::class, 'store'])       ->name('store');
            Route::get('/list',                     [GlobalRegionController::class, 'listForEditor'])->name('list');
            Route::get('/{region}/editor',          [GlobalRegionController::class, 'editor'])      ->name('editor');
            Route::post('/{region}/save',           [GlobalRegionController::class, 'save'])        ->name('save');
            Route::post('/{region}/publish',        [GlobalRegionController::class, 'publish'])     ->name('publish');
            Route::post('/{region}/unpublish',      [GlobalRegionController::class, 'unpublish'])   ->name('unpublish');
            Route::get('/{region}/preview',         [GlobalRegionController::class, 'preview'])     ->name('preview');
            Route::get('/{region}/preview-data',    [GlobalRegionController::class, 'previewData']) ->name('preview-data');
            Route::patch('/{region}',               [GlobalRegionController::class, 'update'])      ->name('update');
            Route::delete('/{region}',              [GlobalRegionController::class, 'destroy'])     ->name('destroy');
        });    });
});
