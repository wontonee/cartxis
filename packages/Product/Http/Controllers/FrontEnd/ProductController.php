<?php

namespace Vortex\Product\Http\Controllers\FrontEnd;

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

        // Rating filter - filter by average rating from reviews using subquery
        if ($request->has('rating')) {
            $minRating = $request->rating;
            $query->whereRaw('(SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_reviews.product_id = products.id AND product_reviews.status = ?) >= ?', ['approved', $minRating]);
        }

        // Stock filter - use 'quantity' column
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('quantity', '>', 0);
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

        $brands = Brand::where('status', true)
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
            ->with(['brand', 'categories', 'images', 'attributeValues.attribute.options', 'attributeValues.option'])
            ->firstOrFail();

        // Get configurable attributes (for product variations like color, size)
        $configurableAttributes = \Vortex\Product\Models\Attribute::with('options')
            ->where('is_configurable', true)
            ->whereHas('productValues', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'type', 'is_required']);

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
            'configurableAttributes' => $configurableAttributes,
        ]);
    }

    /**
     * Get product quick view data (for modal)
     */
    public function quickView($slug)
    {
        try {
            $product = Product::where('slug', $slug)
                ->where('status', 'enabled')
                ->with(['brand', 'categories', 'images'])
                ->firstOrFail();

            // Load configurable attributes if they exist
            $configurableAttributes = [];
            if ($product->has_configurable_attributes) {
                $attributeValues = $product->attributeValues()
                    ->whereHas('attribute', function($query) {
                        $query->where('is_configurable', true);
                    })
                    ->with(['attribute', 'option'])
                    ->get();

                $configurableAttributes = $attributeValues
                    ->groupBy('attribute_id')
                    ->map(function($values) {
                        $attribute = $values->first()->attribute;
                        if (!$attribute) {
                            return null;
                        }

                        return [
                            'id' => $attribute->id,
                            'name' => $attribute->name,
                            'type' => $attribute->type,
                            'options' => $values->map(function($value) {
                                // Use the option relationship (which points to attribute_option_id)
                                if ($value->option) {
                                    return [
                                        'id' => $value->option->id,
                                        'value' => $value->option->value ?? $value->option->label,
                                        'color_code' => $value->option->swatch_value ?? null,
                                    ];
                                } elseif ($value->text_value) {
                                    // Fallback for text attributes without option
                                    return [
                                        'id' => $value->id,
                                        'value' => $value->text_value,
                                        'color_code' => null,
                                    ];
                                }
                                return null;
                            })->filter()->values()
                        ];
                    })
                    ->filter()
                    ->values();
            }

            return response()->json([
                'product' => $product,
                'configurableAttributes' => $configurableAttributes,
            ]);
        } catch (\Exception $e) {
            \Log::error('QuickView Error: ' . $e->getMessage(), [
                'slug' => $slug,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load product details',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
