<?php

namespace Cartxis\Shop\Http\Controllers;

use Cartxis\Shop\Services\ProductService;
use Cartxis\Shop\Services\CategoryService;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\Product\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var CategoryService
     */
    protected $categoryService;
    
    /**
     * @var ThemeViewResolver
     */
    protected $themeResolver;

    /**
     * Create a new controller instance.
     *
     * @param ProductService $productService
     * @param CategoryService $categoryService
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(
        ProductService $productService, 
        CategoryService $categoryService,
        ThemeViewResolver $themeResolver
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display product listing.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', config('shop.listing.products_per_page', 12));
        $sort = $request->input('sort', config('shop.listing.default_sort', 'newest'));

        // Build filters array
        $filters = [
            'category' => $request->input('category'),
            'brand' => $request->input('brand'),
            'search' => $request->input('search'),
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'rating' => $request->input('rating'),
            'in_stock' => $request->input('in_stock'),
        ];

        // Get products with filters applied
        $products = $this->productService->getAllProducts($perPage, $sort, $filters);
        
        // Get all categories with actual product count from database
            $categories = \Cartxis\Product\Models\Category::withCount(['products' => function($query) {
                $query->where('status', 'enabled');
            }])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products_count' => $category->products_count,
                ];
            });
        
        // Get all brands with product count
        $brands = Brand::withCount(['products' => function($query) {
                $query->where('status', 'enabled');
            }])
            ->where('status', 1)
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'products_count' => $brand->products_count,
                ];
            });
        
        return Inertia::render($this->themeResolver->resolve('Products/Index'), [
            'products' => $products,
            'filters' => [
                'categories' => $categories,
                'brands' => $brands,
                'priceRange' => [
                    'min' => 0,
                    'max' => 1000,
                ],
            ],
            'activeFilters' => [
                'category' => $request->input('category'),
                'brand' => $request->input('brand'),
                'search' => $request->input('search'),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'rating' => $request->input('rating'),
                'in_stock' => $request->input('in_stock'),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Display product detail page.
     *
     * @param string $slug
     * @return \Inertia\Response
     */
    public function show($slug)
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            abort(404, 'Product not found');
        }

        $relatedProducts = $this->productService->getRelatedProducts($product->id);

        // Get configurable attributes if product has them
        $configurableAttributes = [];
        
        if ($product->has_configurable_attributes) {
            // Get unique attributes from attributeValues
            $attributes = $product->attributeValues()
                ->with(['attribute.options'])
                ->get()
                ->groupBy('attribute_id')
                ->map(function ($values) {
                    $firstValue = $values->first();
                    $attribute = $firstValue->attribute;
                    
                    return [
                        'id' => $attribute->id,
                        'label' => $attribute->name,
                        'code' => $attribute->code ?? strtolower(str_replace(' ', '_', $attribute->name)),
                        'type' => $attribute->type ?? 'select',
                        'is_required' => $attribute->is_required ?? true,
                        'options' => $attribute->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'label' => $option->label,
                                'value' => $option->value,
                                'swatch_value' => $option->swatch_value ?? null,
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();
            
            $configurableAttributes = $attributes;
        }

        // Map approvedReviews to reviews for frontend
        $productData = $product->toArray();
        $productData['reviews'] = collect($productData['approved_reviews'] ?? [])->map(function ($r) {
            return [
                'id' => $r['id'],
                'author' => $r['reviewer_name'] ?? 'Anonymous',
                'rating' => $r['rating'],
                'title' => $r['title'] ?? '',
                'comment' => $r['comment'] ?? '',
                'created_at' => $r['created_at'],
            ];
        })->values()->toArray();
        $productData['reviews_count'] = count($productData['reviews']);
        $productData['rating'] = count($productData['reviews']) > 0
            ? round(collect($productData['reviews'])->avg('rating'), 1)
            : 0;
        unset($productData['approved_reviews']);

        return Inertia::render($this->themeResolver->resolve('Products/Show'), [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'configurableAttributes' => $configurableAttributes,
            'seo' => [
                'title' => $product->name,
                'description' => $product->meta_description ?? substr(strip_tags($product->description), 0, 160),
                'keywords' => $product->meta_keywords ?? '',
            ],
        ]);
    }
    
    /**
     * Get product data for quick view modal.
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickView($slug)
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        // Get configurable attributes if product has them
        $configurableAttributes = [];
        
        if ($product->has_configurable_attributes) {
            // Get unique attributes from attributeValues
            $attributes = $product->attributeValues()
                ->with(['attribute.options'])
                ->get()
                ->groupBy('attribute_id')
                ->map(function ($values) {
                    $firstValue = $values->first();
                    $attribute = $firstValue->attribute;
                    
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'type' => $attribute->type ?? 'select',
                        'options' => $attribute->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'value' => $option->value,
                                'color_code' => $option->color_code ?? null,
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();
            
            $configurableAttributes = $attributes;
        }

        return response()->json([
            'product' => $product,
            'configurableAttributes' => $configurableAttributes,
        ]);
    }
}
