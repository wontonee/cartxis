<?php

namespace Vortex\Shop\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Vortex\Shop\Repositories\ProductRepository;

class SearchController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get search suggestions based on query
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([
                'suggestions' => []
            ]);
        }

        $products = $this->productRepository->model()
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'slug', 'price', 'special_price')
            ->with('mainImage:id,product_id,path')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->special_price ?? $product->price,
                    'image' => $product->image,
                ];
            });

        return response()->json([
            'suggestions' => $products
        ]);
    }
}
