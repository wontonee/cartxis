<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\API\Http\Resources\ProductResource;
use Cartxis\Product\Models\Product;

class SearchController extends Controller
{
    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $minLength = config('cartxis-api.search.min_query_length', 2);

        if (strlen($query) < $minLength) {
            return ApiResponse::error(
                "Search query must be at least {$minLength} characters",
                null,
                400,
                'QUERY_TOO_SHORT'
            );
        }

        $perPage = min(
            $request->get('per_page', config('cartxis-api.search.results_per_page', 20)),
            config('cartxis-api.pagination.max_per_page')
        );

        $products = Product::query()
            ->where('status', 'enabled')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with(['images', 'brand', 'categories'])
            ->paginate($perPage);

        return ApiResponse::paginated(
            $products->through(fn($product) => new ProductResource($product)),
            "Search results for '{$query}'"
        );
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        $minLength = config('cartxis-api.search.min_query_length', 2);

        if (strlen($query) < $minLength) {
            return ApiResponse::success([], 'No suggestions');
        }

        $limit = min($request->get('limit', 10), config('cartxis-api.search.suggestions_limit', 10));

        $suggestions = Product::query()
            ->where('status', 'enabled')
            ->where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'sku')
            ->limit($limit)
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ]);

        return ApiResponse::success(
            $suggestions,
            'Search suggestions retrieved successfully'
        );
    }
}
