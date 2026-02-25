<?php

namespace Cartxis\Shop\Http\Controllers;

use Cartxis\Shop\Services\CategoryService;
use Cartxis\Shop\Services\ProductService;
use Cartxis\Core\Services\ThemeViewResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var ThemeViewResolver
     */
    protected $themeResolver;

    /**
     * Create a new controller instance.
     *
     * @param CategoryService $categoryService
     * @param ProductService $productService
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(
        CategoryService $categoryService,
        ProductService $productService,
        ThemeViewResolver $themeResolver
    ) {
        $this->categoryService = $categoryService;
        $this->productService = $productService;
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display category page with products.
     *
     * @param string $slug
     * @param Request $request
     * @return \Inertia\Response
     */
    public function show($slug, Request $request)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);

        if (!$category) {
            abort(404, 'Category not found');
        }

        $filters = [
            'per_page' => $request->input('per_page', config('shop.listing.products_per_page', 12)),
            'sort' => $request->input('sort', config('shop.listing.default_sort', 'position')),
        ];

        $products = $this->productService->getProductsByCategory($category->id, $filters);

        return Inertia::render($this->themeResolver->resolve('Category/Show'), [
            'category' => $category,
            'products' => $products,
            'filters' => $filters,
            'seo' => [
                'title' => $category->meta_title ?? $category->name,
                'description' => $category->meta_description ?? substr(strip_tags($category->description ?? ''), 0, 160),
                'keywords' => $category->meta_keywords ?? '',
            ],
        ]);
    }
}
