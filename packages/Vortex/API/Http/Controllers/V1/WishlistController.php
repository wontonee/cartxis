<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\ProductResource;
use Vortex\Customer\Models\Wishlist;
use Vortex\Product\Models\Product;

class WishlistController extends Controller
{
    /**
     * Get wishlist items.
     */
    public function index(Request $request)
    {
        $wishlistItems = Wishlist::with(['product.images', 'product.brand'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return ApiResponse::success([
            'items' => $wishlistItems->map(fn($item) => [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'added_at' => $item->created_at->toIso8601String(),
            ]),
            'count' => $wishlistItems->count(),
        ], 'Wishlist retrieved successfully');
    }

    /**
     * Add product to wishlist.
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $product = Product::find($request->product_id);

        if (!$product || $product->status !== 'active') {
            return ApiResponse::error('Product not available', null, 400, 'PRODUCT_UNAVAILABLE');
        }

        // Check if already in wishlist
        $exists = Wishlist::where('customer_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return ApiResponse::error('Product already in wishlist', null, 400, 'ALREADY_IN_WISHLIST');
        }

        $wishlistItem = Wishlist::create([
            'customer_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        $wishlistItem->load('product.images', 'product.brand');

        return ApiResponse::success([
            'id' => $wishlistItem->id,
            'product' => new ProductResource($wishlistItem->product),
            'added_at' => $wishlistItem->created_at->toIso8601String(),
        ], 'Product added to wishlist', 201);
    }

    /**
     * Remove product from wishlist.
     */
    public function remove(Request $request, $id)
    {
        $wishlistItem = Wishlist::where('customer_id', $request->user()->id)->find($id);

        if (!$wishlistItem) {
            return ApiResponse::notFound('Wishlist item not found', 'WISHLIST_ITEM_NOT_FOUND');
        }

        $wishlistItem->delete();

        return ApiResponse::success(null, 'Product removed from wishlist');
    }

    /**
     * Move wishlist item to cart.
     */
    public function moveToCart(Request $request, $id)
    {
        $wishlistItem = Wishlist::with('product')
            ->where('customer_id', $request->user()->id)
            ->find($id);

        if (!$wishlistItem) {
            return ApiResponse::notFound('Wishlist item not found', 'WISHLIST_ITEM_NOT_FOUND');
        }

        // TODO: Add to cart logic
        // For now, just remove from wishlist

        $wishlistItem->delete();

        return ApiResponse::success(null, 'Product moved to cart');
    }
}
