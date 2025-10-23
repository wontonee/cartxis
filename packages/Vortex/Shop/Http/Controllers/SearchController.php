<?php

namespace Vortex\Shop\Http\Controllers;

use Vortex\Shop\Services\ProductService;
use Vortex\Core\Services\ThemeViewResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
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
     * @param ProductService $productService
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(ProductService $productService, ThemeViewResolver $themeResolver)
    {
        $this->productService = $productService;
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display search results.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $perPage = $request->input('per_page', config('shop.listing.products_per_page', 12));
        
        $minLength = config('shop.search.min_query_length', 3);

        if (strlen($query) < $minLength) {
            return Inertia::render($this->themeResolver->resolve('Search/Index'), [
                'query' => $query,
                'products' => [],
                'message' => trans('shop::shop.search.no_results'),
            ]);
        }

        $products = $this->productService->searchProducts($query, $perPage);

        return Inertia::render($this->themeResolver->resolve('Search/Index'), [
            'query' => $query,
            'products' => $products,
            'resultsCount' => $products->total(),
            'seo' => [
                'title' => trans('shop::shop.search.results_count', ['count' => $products->total()]) . ' - ' . $query,
                'description' => 'Search results for: ' . $query,
            ],
        ]);
    }
}
