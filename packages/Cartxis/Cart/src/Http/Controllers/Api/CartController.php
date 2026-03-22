<?php

namespace Cartxis\Cart\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Cartxis\Product\Models\Product;
use Cartxis\Marketing\Services\CouponService;

class CartController extends Controller
{
    /**
     * Get cart items (session for guests, database for users)
     */
    public function index()
    {
        $items = $this->getCartItems();
        $items = $this->refreshCartItemImages($items);
        $coupon = $this->getCartCoupon();

        return response()->json([
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
            'coupon' => $coupon,
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1',
            'attributes' => 'array',
        ]);

        $product = Product::with(['images', 'mainImage'])->findOrFail($validated['product_id']);
        $quantity = $validated['quantity'] ?? 1;
        $productAttributes = $validated['attributes'] ?? [];

        // Check stock
        if ($product->quantity < $quantity) {
            return response()->json([
                'message' => 'Insufficient stock available',
            ], 400);
        }

        // Convert attribute IDs to readable format
        $readableAttributes = $this->convertAttributesToReadable($productAttributes);

        $cartItem = [
            'id' => uniqid('cart_'),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_image' => $product->image,
            'price' => $product->special_price ?? $product->price,
            'quantity' => $quantity,
            'attributes' => $readableAttributes,
            'tax_class_id' => $product->tax_class_id,
            'weight' => $product->weight,
        ];

        $items = $this->getCartItems();

        // Check if product already exists in cart
        $existingKey = $this->findExistingCartItem($items, $product->id, $productAttributes);

        if ($existingKey !== null) {
            // Update quantity
            $items[$existingKey]['quantity'] += $cartItem['quantity'];
        } else {
            // Add new item
            $items[] = $cartItem;
        }

        $this->saveCartItems($items);

        $this->recalculateCoupon($items);

        return response()->json([
            'message' => 'Product added to cart successfully',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
            'coupon' => $this->getCartCoupon(),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $items = $this->getCartItems();
        $key = $this->findCartItemById($items, $itemId);

        if ($key === null) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        // Check stock
        $product = Product::find($items[$key]['product_id']);
        if ($product && $product->quantity < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock available',
            ], 400);
        }

        $items[$key]['quantity'] = $request->quantity;
        $this->saveCartItems($items);
        $this->recalculateCoupon($items);
        
        $items = $this->refreshCartItemImages($items);

        return response()->json([
            'message' => 'Cart updated successfully',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
            'coupon' => $this->getCartCoupon(),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove($itemId)
    {
        $items = $this->getCartItems();
        $key = $this->findCartItemById($items, $itemId);

        if ($key === null) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        array_splice($items, $key, 1);
        $this->saveCartItems($items);
        $this->recalculateCoupon($items);
        
        $items = $this->refreshCartItemImages($items);

        return response()->json([
            'message' => 'Item removed from cart',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
            'coupon' => $this->getCartCoupon(),
        ]);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->saveCartItems([]);
        $this->clearCartCoupon();

        return response()->json([
            'message' => 'Cart cleared successfully',
            'items' => [],
            'count' => 0,
            'subtotal' => 0,
            'coupon' => null,
        ]);
    }

    /**
     * Apply a coupon code to the session cart.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string|max:50']);

        $items = $this->getCartItems();

        if (empty($items)) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);
        $cartItemsCollection = collect($items)->map(fn($i) => (object) $i);
        $customerId = Auth::id();

        $couponService = app(CouponService::class);
        $result = $couponService->validate(
            Str::upper(trim($request->coupon_code)),
            $customerId,
            $subtotal,
            $cartItemsCollection
        );

        if (!$result['valid']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $applied = $couponService->apply($result['coupon'], $subtotal, $cartItemsCollection);

        $coupon = [
            'code'            => $applied['coupon_code'],
            'discount_amount' => $applied['discount_amount'],
            'message'         => $applied['message'],
        ];

        $this->saveCartCoupon($coupon);

        return response()->json([
            'message' => 'Coupon applied successfully.',
            'coupon'  => $coupon,
        ]);
    }

    /**
     * Remove the currently applied coupon from the session cart.
     */
    public function removeCoupon()
    {
        $this->clearCartCoupon();

        return response()->json(['message' => 'Coupon removed.', 'coupon' => null]);
    }

    /**
     * Get cart items from session or database
     */
    private function getCartItems(): array
    {
        if (Auth::check()) {
            // TODO: Implement database cart for authenticated users
            // For now, using session for both
            return Session::get('cart', []);
        }

        return Session::get('cart', []);
    }

    /**
     * Save cart items to session or database
     */
    private function saveCartItems(array $items): void
    {
        if (Auth::check()) {
            // TODO: Implement database cart for authenticated users
            // For now, using session for both
            Session::put('cart', $items);
        } else {
            Session::put('cart', $items);
        }
    }

    /**
     * Recalculate coupon discount against the current cart subtotal.
     * Called after any cart mutation to keep discount in sync.
     */
    private function recalculateCoupon(array $items): void
    {
        $coupon = $this->getCartCoupon();
        if (!$coupon || empty($coupon['code'])) {
            return;
        }

        if (empty($items)) {
            $this->clearCartCoupon();
            return;
        }

        $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);
        $cartItemsCollection = collect($items)->map(fn($i) => (object) $i);

        try {
            $couponService = app(CouponService::class);
            $result = $couponService->validate(
                $coupon['code'],
                Auth::id(),
                $subtotal,
                $cartItemsCollection
            );

            if ($result['valid']) {
                $applied = $couponService->apply($result['coupon'], $subtotal, $cartItemsCollection);
                $this->saveCartCoupon([
                    'code'            => $applied['coupon_code'],
                    'discount_amount' => $applied['discount_amount'],
                    'message'         => $applied['message'],
                ]);
            } else {
                // Coupon no longer valid (e.g. min spend no longer met) — remove it
                $this->clearCartCoupon();
            }
        } catch (\Exception $e) {
            // Silently ignore — do not break the cart operation
        }
    }

    /**
     * Get applied coupon from session.
     */
    private function getCartCoupon(): ?array
    {
        return Session::get('cart_coupon');
    }

    /**
     * Persist coupon data in session.
     */
    private function saveCartCoupon(array $coupon): void
    {
        Session::put('cart_coupon', $coupon);
    }

    /**
     * Remove coupon from session.
     */
    private function clearCartCoupon(): void
    {
        Session::forget('cart_coupon');
    }

    /**
     * Find existing cart item by product ID and attributes
     */
    private function findExistingCartItem(array $items, int $productId, array $attributes): ?int
    {
        foreach ($items as $key => $item) {
            if ($item['product_id'] === $productId) {
                // Check if attributes match
                $itemAttrs = $item['attributes'] ?? [];
                if (json_encode($itemAttrs) === json_encode($attributes)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Find cart item by ID
     */
    private function findCartItemById(array $items, string $itemId): ?int
    {
        foreach ($items as $key => $item) {
            if ($item['id'] === $itemId) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Convert attribute IDs to readable format
     * Converts {attribute_id: option_id} to {"Attribute Name": "Option Label"}
     */
    private function convertAttributesToReadable(array $attributes): array
    {
        if (empty($attributes)) {
            return [];
        }

        $readable = [];

        foreach ($attributes as $attributeId => $optionId) {
            // Get the attribute and option details
            $attribute = \Cartxis\Product\Models\Attribute::find($attributeId);
            $option = \Cartxis\Product\Models\AttributeOption::find($optionId);

            if ($attribute && $option) {
                $readable[$attribute->name] = $option->label;
            }
        }

        return $readable;
    }

    /**
     * Refresh product images in cart items
     * This ensures cart items always have the latest product images
     */
    private function refreshCartItemImages(array $items): array
    {
        return array_map(function ($item) {
            $product = Product::with(['images', 'mainImage'])->find($item['product_id']);
            if ($product) {
                $item['product_image'] = $product->image;
            }
            return $item;
        }, $items);
    }
}
