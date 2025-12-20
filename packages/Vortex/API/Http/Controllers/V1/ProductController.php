<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\ProductResource;
use Vortex\Product\Models\Product;

class ProductController extends Controller
{
    /**
     * Get paginated list of products.
     */
    public function index(Request $request)
    {
        $perPage = min($request->get('per_page', 20), config('vortex-api.pagination.max_per_page'));
        
        $query = Product::query()
            ->where('status', 'active')
            ->where('visible_individually', true);

        // Filtering
        if ($request->has('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['price', 'name', 'created_at', 'popularity'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'popularity') {
                $query->withCount('orderItems')->orderBy('order_items_count', $sortOrder);
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

        if ($product->status !== 'active') {
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
            ->where('status', 'active')
            ->where('featured', true)
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
        $perPage = min($request->get('per_page', 20), config('vortex-api.pagination.max_per_page'));

        $products = Product::query()
            ->where('status', 'active')
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
            ->where('status', 'active')
            ->where('new', true)
            ->orWhere('created_at', '>=', now()->subDays(30))
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
            ->where('status', 'active')
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
