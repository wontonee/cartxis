<?php

namespace Vortex\Cart\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Vortex\Product\Models\Product;

class CartController extends Controller
{
    /**
     * Get cart items (session for guests, database for users)
     */
    public function index()
    {
        $items = $this->getCartItems();

        return response()->json([
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
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

        $product = Product::findOrFail($validated['product_id']);
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

        return response()->json([
            'message' => 'Product added to cart successfully',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
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

        return response()->json([
            'message' => 'Cart updated successfully',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
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

        return response()->json([
            'message' => 'Item removed from cart',
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => collect($items)->sum(fn($item) => $item['price'] * $item['quantity']),
        ]);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->saveCartItems([]);

        return response()->json([
            'message' => 'Cart cleared successfully',
            'items' => [],
            'count' => 0,
            'subtotal' => 0,
        ]);
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
            $attribute = \Vortex\Product\Models\Attribute::find($attributeId);
            $option = \Vortex\Product\Models\AttributeOption::find($optionId);

            if ($attribute && $option) {
                $readable[$attribute->name] = $option->label;
            }
        }

        return $readable;
    }
}
