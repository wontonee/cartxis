<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Cartxis\Product\Models\Product;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── Public: Products by IDs (used by ProductsGrid block on storefront) ─────
Route::get('/products/by-ids', function (Request $request) {
    $ids = array_filter(array_map('intval', explode(',', $request->input('ids', ''))));
    if (empty($ids)) {
        return response()->json(['data' => []]);
    }
    $cols = ['id', 'name', 'slug', 'price', 'thumbnail'];
    $products = Product::select($cols)
        ->where('status', 'active')
        ->whereIn('id', $ids)
        ->get()
        ->map(fn($p) => array_merge($p->toArray(), [
            'formatted_price' => '$' . number_format($p->price, 2),
        ]));
    return response()->json(['data' => $products]);
})->middleware('throttle:60,1');
