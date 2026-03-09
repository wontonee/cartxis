<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\Category;
use Cartxis\Core\Models\Currency;

/*
|--------------------------------------------------------------------------
| Shop API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all API routes for the Shop package.
| These routes are loaded by the ShopServiceProvider.
|
*/

Route::group([
    'prefix' => 'api/shop',
    'middleware' => ['api', 'throttle:60,1'],
    'as' => 'api.shop.'
], function () {

    /*
    |--------------------------------------------------------------------------
    | Product API Routes
    |--------------------------------------------------------------------------
    */

    // Products by explicit IDs (used by ProductsGrid block — manual source)
    Route::get('/products/by-ids', function (Request $request) {
        $ids = array_filter(array_map('intval', explode(',', $request->input('ids', ''))));
        if (empty($ids)) {
            return response()->json(['data' => []]);
        }
        $currency = Currency::getDefault();
        $symbol   = $currency?->symbol ?? '₹';
        $decimals = $currency?->decimal_places ?? 2;
        $products = Product::select(['id', 'name', 'slug', 'price', 'main_image_id'])
            ->with('mainImage:id,path,thumbnail_path')
            ->where('status', 'enabled')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'slug'            => $p->slug,
                'price'           => (float) $p->price,
                'thumbnail'       => $p->mainImage ? (filter_var($p->mainImage->thumbnail_path ?? $p->mainImage->path, FILTER_VALIDATE_URL) ? ($p->mainImage->thumbnail_path ?? $p->mainImage->path) : asset('storage/' . ($p->mainImage->thumbnail_path ?? $p->mainImage->path))) : null,
                'formatted_price' => $symbol . number_format((float) $p->price, $decimals),
            ]);
        return response()->json(['data' => $products]);
    })->name('products.by-ids');

    // Featured products (used by ProductsGrid block — featured source)
    Route::get('/products/featured', function (Request $request) {
        $limit    = min((int) $request->input('limit', 8), 24);
        $currency = Currency::getDefault();
        $symbol   = $currency?->symbol ?? '₹';
        $decimals = $currency?->decimal_places ?? 2;
        $products = Product::select(['id', 'name', 'slug', 'price', 'main_image_id'])
            ->with('mainImage:id,path,thumbnail_path')
            ->where('status', 'enabled')
            ->where('featured', true)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'slug'            => $p->slug,
                'price'           => (float) $p->price,
                'thumbnail'       => $p->mainImage ? (filter_var($p->mainImage->thumbnail_path ?? $p->mainImage->path, FILTER_VALIDATE_URL) ? ($p->mainImage->thumbnail_path ?? $p->mainImage->path) : asset('storage/' . ($p->mainImage->thumbnail_path ?? $p->mainImage->path))) : null,
                'formatted_price' => $symbol . number_format((float) $p->price, $decimals),
            ]);
        return response()->json(['data' => $products]);
    })->name('products.featured');

    // Latest products (used by ProductsGrid block — latest source)
    Route::get('/products/latest', function (Request $request) {
        $limit    = min((int) $request->input('limit', 8), 24);
        $currency = Currency::getDefault();
        $symbol   = $currency?->symbol ?? '₹';
        $decimals = $currency?->decimal_places ?? 2;
        $products = Product::select(['id', 'name', 'slug', 'price', 'main_image_id'])
            ->with('mainImage:id,path,thumbnail_path')
            ->where('status', 'enabled')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'slug'            => $p->slug,
                'price'           => (float) $p->price,
                'thumbnail'       => $p->mainImage ? (filter_var($p->mainImage->thumbnail_path ?? $p->mainImage->path, FILTER_VALIDATE_URL) ? ($p->mainImage->thumbnail_path ?? $p->mainImage->path) : asset('storage/' . ($p->mainImage->thumbnail_path ?? $p->mainImage->path))) : null,
                'formatted_price' => $symbol . number_format((float) $p->price, $decimals),
            ]);
        return response()->json(['data' => $products]);
    })->name('products.latest');

    /*
    |--------------------------------------------------------------------------
    | Category API Routes
    |--------------------------------------------------------------------------
    */

    // All categories (used by CategoriesGrid block)
    Route::get('/categories', function (Request $request) {
        $limit      = min((int) $request->input('limit', 8), 32);
        $categories = Category::select(['id', 'name', 'slug', 'image'])
            ->withCount('products')
            ->oldest('name')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'slug'           => $c->slug,
                'image_url'      => $c->image_url,
                'products_count' => $c->products_count,
            ]);
        return response()->json(['data' => $categories]);
    })->name('categories.index');

    /*
    |--------------------------------------------------------------------------
    | Search API Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/search', [Cartxis\Shop\Http\Controllers\Api\SearchController::class, 'index'])->name('search');
    // Route::get('/search/suggestions', [Cartxis\Shop\Http\Controllers\Api\SearchController::class, 'suggestions'])->name('search.suggestions');

    /*
    |--------------------------------------------------------------------------
    | Wishlist API Routes
    |--------------------------------------------------------------------------
    */
    // Route::middleware(['auth:sanctum'])->group(function () {
    //     Route::get('/wishlist', [Cartxis\Shop\Http\Controllers\Api\WishlistController::class, 'index'])->name('wishlist.index');
    //     Route::post('/wishlist', [Cartxis\Shop\Http\Controllers\Api\WishlistController::class, 'store'])->name('wishlist.store');
    //     Route::delete('/wishlist/{id}', [Cartxis\Shop\Http\Controllers\Api\WishlistController::class, 'destroy'])->name('wishlist.destroy');
    // });
});
