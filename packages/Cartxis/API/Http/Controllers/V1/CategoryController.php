<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\API\Http\Resources\CategoryResource;
use Cartxis\Product\Models\Category;

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

        // Search by name or slug
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by parent
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } elseif (!$request->has('search')) {
            // Root categories by default (unless searching)
            $query->whereNull('parent_id');
        }

        // Add product count
        if ($request->get('with_product_count', false)) {
            $query->withCount('products');
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
            ->where('status', 'enabled')
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->where('status', 'enabled')
                  ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
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
        $category = Category::with(['parent', 'children' => fn($q) => $q->where('status', 'enabled')])
            ->find($id);

        if (!$category) {
            return ApiResponse::notFound('Category not found', 'CATEGORY_NOT_FOUND');
        }

        if ($category->status !== 'enabled') {
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

        $perPage = min($request->get('per_page', 20), config('cartxis-api.pagination.max_per_page'));

        // Get all subcategory IDs recursively
        $categoryIds = $this->getAllCategoryIds($category);

            $products = \Cartxis\Product\Models\Product::query()
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('status', 'enabled')
            ->with(['images', 'brand'])
            ->paginate($perPage);

        return ApiResponse::paginated(
                $products->through(fn($product) => new \Cartxis\API\Http\Resources\ProductResource($product)),
            "Products in '{$category->name}' retrieved successfully"
        );
    }

    /**
     * Get all category IDs including subcategories recursively.
     */
    private function getAllCategoryIds($category)
    {
        $ids = [$category->id];
        
        $subcategories = Category::where('parent_id', $category->id)->get();
        
        foreach ($subcategories as $subcategory) {
            $ids = array_merge($ids, $this->getAllCategoryIds($subcategory));
        }
        
        return $ids;
    }
}
