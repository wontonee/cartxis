<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Cartxis\Product\Models\Product;

class ProductsSearchController extends Controller
{
    /**
     * Search products for use in the Products Grid block settings panel.
     */
    public function index(Request $request): JsonResponse
    {
        $search  = $request->input('search', '');
        $perPage = min((int) $request->input('per_page', 12), 50);

        $query = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'thumbnail'])
            ->where('status', 'active');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json($products);
    }
}
