<?php

namespace Vortex\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Vortex\Product\Models\Product;
use Vortex\Product\Models\Category;
use Vortex\Product\Models\Brand;

class ProductController extends Controller
{
    /**
     * Display product listing with filters and sorting
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('products.status', 'enabled');

        // Apply filters
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });
            }
        }

        if ($request->has('brand')) {
            $brand = Brand::where('slug', $request->brand)->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Price filter
        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Rating filter
        if ($request->has('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        // Stock filter
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock_quantity', '>', 0);
        }

        // Sorting
        $sortField = $request->get('sort', 'newest');

        switch ($sortField) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'rating':
                // Sort by average rating using subquery to avoid ambiguous columns
                $query->addSelect([
                    'avg_rating' => \DB::table('product_reviews')
                        ->selectRaw('COALESCE(AVG(rating), 0)')
                        ->whereColumn('product_reviews.product_id', 'products.id')
                        ->where('product_reviews.status', 'approved')
                ])
                ->orderByDesc('avg_rating');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->with(['brand', 'categories'])
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options
        $categories = Category::where('status', 'enabled')
            ->withCount('products')
            ->get();

        $brands = Brand::where('status', 'enabled')
            ->withCount('products')
            ->get();

        // Price range
        $priceRange = [
            'min' => Product::where('status', 'enabled')->min('price') ?? 0,
            'max' => Product::where('status', 'enabled')->max('price') ?? 1000,
        ];

        return Inertia::render('Frontend/Products/Index', [
            'products' => $products,
            'filters' => [
                'categories' => $categories,
                'brands' => $brands,
                'priceRange' => $priceRange,
            ],
            'activeFilters' => [
                'category' => $request->category,
                'brand' => $request->brand,
                'search' => $request->search,
                'price_min' => $request->price_min,
                'price_max' => $request->price_max,
                'rating' => $request->rating,
                'in_stock' => $request->in_stock,
                'sort' => $sortField,
            ],
        ]);
    }

    /**
     * Display product detail page
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'enabled')
            ->with(['brand', 'categories', 'images'])
            ->firstOrFail();

        // Related products (same category)
        $relatedProducts = Product::where('status', 'enabled')
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($query) use ($product) {
                $query->whereIn('categories.id', $product->categories->pluck('id'));
            })
            ->limit(8)
            ->get();

        return Inertia::render('Frontend/Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
