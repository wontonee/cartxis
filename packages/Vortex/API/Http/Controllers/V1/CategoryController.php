<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\CategoryResource;
use Vortex\Product\Models\Category;

class CategoryController extends Controller
{
    /**
     * Get list of categories.
     */
    public function index(Request $request)
    {
        $query = Category::query()
            ->where('status', 'enabled')
            ->with(['parent', 'children']);

        // Filter by parent
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            // Root categories by default
            $query->whereNull('parent_id');
        }

        $categories = $query->orderBy('sort_order')->get();

        return ApiResponse::success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Get category tree structure.
     */
    public function tree()
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->where('status', 'active')
                  ->orderBy('position');
            }])
            ->orderBy('position')
            ->get();

        return ApiResponse::success(
            CategoryResource::collection($categories),
            'Category tree retrieved successfully'
        );
    }

    /**
     * Get single category details.
     */
    public function show($id)
    {
        $category = Category::with(['parent', 'children' => fn($q) => $q->where('status', 'active')])
            ->find($id);

        if (!$category) {
            return ApiResponse::notFound('Category not found', 'CATEGORY_NOT_FOUND');
        }

        if ($category->status !== 'active') {
            return ApiResponse::error('Category is not available', null, 403, 'CATEGORY_UNAVAILABLE');
        }

        return ApiResponse::success(
            new CategoryResource($category),
            'Category details retrieved successfully'
        );
    }

    /**
     * Get products in a category.
     */
    public function products(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return ApiResponse::notFound('Category not found', 'CATEGORY_NOT_FOUND');
        }

        $perPage = min($request->get('per_page', 20), config('vortex-api.pagination.max_per_page'));

        $products = $category->products()
            ->where('status', 'enabled')
            ->with(['images', 'brand'])
            ->paginate($perPage);

        return ApiResponse::paginated(
            $products->through(fn($product) => new \Vortex\API\Http\Resources\ProductResource($product)),
            "Products in '{$category->name}' retrieved successfully"
        );
    }
}
