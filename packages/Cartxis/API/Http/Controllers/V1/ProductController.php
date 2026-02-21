<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\API\Http\Resources\ProductResource;
use Cartxis\Product\Models\Product;

class ProductController extends Controller
{
    /**
     * Get paginated list of products.
     */
    public function index(Request $request)
    {
        $perPage = min($request->get('per_page', 20), config('cartxis-api.pagination.max_per_page'));
        
        $query = Product::query()
            ->where('status', 'enabled')
            ->where('price', '>', 0)
            ->where('quantity', '>', 0);

        // Search by name or SKU
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('featured', filter_var($request->featured, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by new arrivals
        if ($request->has('new')) {
            $query->where('new', filter_var($request->new, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by on sale
        if ($request->has('on_sale') && filter_var($request->on_sale, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('special_price', '>', 0)
                  ->whereColumn('special_price', '<', 'price');
        }

        // Filtering by category
        if ($request->has('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }

        // Filter by multiple categories
        if ($request->has('category_ids')) {
            $categoryIds = is_array($request->category_ids) 
                ? $request->category_ids 
                : explode(',', $request->category_ids);
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        // Price range filtering
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Brand filtering
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Multiple brands
        if ($request->has('brand_ids')) {
            $brandIds = is_array($request->brand_ids) 
                ? $request->brand_ids 
                : explode(',', $request->brand_ids);
            $query->whereIn('brand_id', $brandIds);
        }

        // Rating filter
        if ($request->has('min_rating')) {
            $query->whereHas('reviews', function($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->min_rating]);
            });
        }

        // Stock status
        if ($request->has('in_stock') && filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('quantity', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['price', 'name', 'created_at', 'popularity', 'rating', 'discount'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'popularity') {
                $query->withCount('orderItems')->orderBy('order_items_count', $sortOrder);
            } elseif ($sortBy === 'rating') {
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
            } elseif ($sortBy === 'discount') {
                $query->orderByRaw('((price - special_price) / price * 100) ' . $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $products = $query->with(['images', 'brand', 'categories'])->paginate($perPage);

        return ApiResponse::paginated(
            $products->through(fn($product) => new ProductResource($product)),
            'Products retrieved successfully'
        );
    }

    /**
     * Get single product details.
     */
    public function show($id)
    {
        $product = Product::with([
            'images',
            'brand',
            'categories',
            'variants',
            'attributes',
            'reviews' => fn($q) => $q->latest()->limit(5),
        ])->find($id);

        if (!$product) {
            return ApiResponse::notFound('Product not found', 'PRODUCT_NOT_FOUND');
        }

        if ($product->status !== 'enabled') {
            return ApiResponse::error('Product is not available', null, 403, 'PRODUCT_UNAVAILABLE');
        }

        return ApiResponse::success(
            new ProductResource($product),
            'Product details retrieved successfully'
        );
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request)
    {
        $limit = min($request->get('limit', 10), 50);

        $products = Product::query()
            ->where('status', 'enabled')
            ->where('featured', true)
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
            ->with(['images', 'brand'])
            ->latest()
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            ProductResource::collection($products),
            'Featured products retrieved successfully'
        );
    }

    /**
     * Get products on sale.
     */
    public function onSale(Request $request)
    {
        $perPage = min($request->get('per_page', 20), config('cartxis-api.pagination.max_per_page'));

        $products = Product::query()
            ->where('status', 'enabled')
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
            ->where('special_price', '>', 0)
            ->whereColumn('special_price', '<', 'price')
            ->where(function($q) {
                $q->whereNull('special_price_from')
                  ->orWhere('special_price_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('special_price_to')
                  ->orWhere('special_price_to', '>=', now());
            })
            ->with(['images', 'brand'])
            ->paginate($perPage);

        return ApiResponse::paginated(
            $products->through(fn($product) => new ProductResource($product)),
            'Sale products retrieved successfully'
        );
    }

    /**
     * Get new arrival products.
     */
    public function newArrivals(Request $request)
    {
        $limit = min($request->get('limit', 20), 50);

        $products = Product::query()
            ->where('status', 'enabled')
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
            ->where(function($q) {
                $q->where('new', true)
                  ->orWhere('created_at', '>=', now()->subDays(30));
            })
            ->with(['images', 'brand'])
            ->latest()
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            ProductResource::collection($products),
            'New arrivals retrieved successfully'
        );
    }

    /**
     * Get related products.
     */
    public function related($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::notFound('Product not found', 'PRODUCT_NOT_FOUND');
        }

        $categoryIds = $product->categories->pluck('id')->toArray();

        $relatedProducts = Product::query()
            ->where('status', 'enabled')
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
            ->where('id', '!=', $id)
            ->whereHas('categories', fn($q) => $q->whereIn('category_id', $categoryIds))
            ->with(['images', 'brand'])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return ApiResponse::success(
            ProductResource::collection($relatedProducts),
            'Related products retrieved successfully'
        );
    }
}
