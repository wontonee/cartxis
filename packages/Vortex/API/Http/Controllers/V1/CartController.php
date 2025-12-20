<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\CartResource;
use Vortex\Cart\Models\Cart;
use Vortex\Cart\Models\CartItem;
use Vortex\Product\Models\Product;

class CartController extends Controller
{
    /**
     * Get current cart.
     */
    public function index(Request $request)
    {
        $cart = Cart::with(['items.product.images', 'items.product.brand'])
            ->firstOrCreate(['user_id' => $request->user()->id]);

        return ApiResponse::success(
            new CartResource($cart),
            'Cart retrieved successfully'
        );
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $product = Product::find($request->product_id);

        if (!$product || $product->status !== 'active') {
            return ApiResponse::error('Product not available', null, 400, 'PRODUCT_UNAVAILABLE');
        }

        // Check stock
        if ($product->track_inventory && $product->quantity < $request->quantity) {
            return ApiResponse::error('Insufficient stock', null, 400, 'INSUFFICIENT_STOCK');
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
            ],
            [
                'quantity' => \DB::raw("quantity + {$request->quantity}"),
                'price' => $product->final_price,
            ]
        );

        $cart->load(['items.product.images', 'items.product.brand']);

        return ApiResponse::success(
            new CartResource($cart),
            'Item added to cart successfully'
        );
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $request->user()->id))
            ->find($itemId);

        if (!$cartItem) {
            return ApiResponse::notFound('Cart item not found', 'CART_ITEM_NOT_FOUND');
        }

        // Check stock
        if ($cartItem->product->track_inventory && $cartItem->product->quantity < $request->quantity) {
            return ApiResponse::error('Insufficient stock', null, 400, 'INSUFFICIENT_STOCK');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        $cart = $cartItem->cart->load(['items.product.images', 'items.product.brand']);

        return ApiResponse::success(
            new CartResource($cart),
            'Cart updated successfully'
        );
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, $itemId)
    {
        $cartItem = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $request->user()->id))
            ->find($itemId);

        if (!$cartItem) {
            return ApiResponse::notFound('Cart item not found', 'CART_ITEM_NOT_FOUND');
        }

        $cartItem->delete();

        $cart = Cart::with(['items.product.images', 'items.product.brand'])
            ->where('user_id', $request->user()->id)
            ->first();

        return ApiResponse::success(
            new CartResource($cart),
            'Item removed from cart successfully'
        );
    }

    /**
     * Clear cart.
     */
    public function clear(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        $cart = Cart::with(['items.product.images', 'items.product.brand'])
            ->firstOrCreate(['user_id' => $request->user()->id]);

        return ApiResponse::success(
            new CartResource($cart),
            'Cart cleared successfully'
        );
    }

    /**
     * Apply coupon to cart.
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $cart = Cart::where('user_id', $request->user()->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty', null, 400, 'CART_EMPTY');
        }

        // TODO: Implement coupon validation and application logic
        // For now, return success
        $cart->update([
            'coupon_code' => $request->coupon_code,
            // 'discount_amount' => $calculatedDiscount,
        ]);

        $cart->load(['items.product.images', 'items.product.brand']);

        return ApiResponse::success(
            new CartResource($cart),
            'Coupon applied successfully'
        );
    }

    /**
     * Remove coupon from cart.
     */
    public function removeCoupon(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->update([
                'coupon_code' => null,
                'discount_amount' => 0,
            ]);
        }

        $cart->load(['items.product.images', 'items.product.brand']);

        return ApiResponse::success(
            new CartResource($cart),
            'Coupon removed successfully'
        );
    }

    /**
     * Get cart items count.
     */
    public function count(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        $count = $cart ? $cart->items->sum('quantity') : 0;

        return ApiResponse::success([
            'count' => $count,
        ], 'Cart count retrieved successfully');
    }
}
