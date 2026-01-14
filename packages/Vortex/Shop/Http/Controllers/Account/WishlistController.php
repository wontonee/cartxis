<?php

declare(strict_types=1);

namespace Vortex\Shop\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Customer\Models\Wishlist;
use Vortex\Product\Models\Product;

class WishlistController extends Controller
{
    /**
     * Display the customer's wishlist.
     */
    public function index(): Response
    {
        return Inertia::render('Wishlist');
    }

    /**
     * Get wishlist items for the authenticated user.
     */
    public function get(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['items' => [], 'count' => 0]);
        }

        // Get or create customer record for this user
        $customer = \Vortex\Customer\Models\Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]
        );

        $wishlistItems = Wishlist::with(['product.images', 'product.brand'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return response()->json([
            'items' => $wishlistItems->map(fn($item) => [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'price' => $item->product->price,
                    'special_price' => $item->product->special_price,
                    'image' => $item->product->images->first()?->url ?? null,
                    'is_in_stock' => $item->product->in_stock ?? ($item->product->stock_status === 'in_stock'),
                ],
                'added_at' => $item->created_at->toIso8601String(),
            ]),
            'count' => $wishlistItems->count(),
        ]);
    }

    /**
     * Add product to wishlist.
     */
    public function add(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Get or create customer record for this user
        $customer = \Vortex\Customer\Models\Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]
        );

        // Check if already in wishlist
        $exists = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already in wishlist'], 400);
        }

        $wishlistItem = Wishlist::create([
            'customer_id' => $customer->id,
            'product_id' => $request->product_id,
        ]);

        $wishlistItem->load('product.images', 'product.brand');

        return response()->json([
            'message' => 'Added to wishlist',
            'item' => [
                'id' => $wishlistItem->id,
                'product' => [
                    'id' => $wishlistItem->product->id,
                    'name' => $wishlistItem->product->name,
                    'slug' => $wishlistItem->product->slug,
                    'price' => $wishlistItem->product->price,
                    'special_price' => $wishlistItem->product->special_price,
                    'image' => $wishlistItem->product->images->first()?->url ?? null,
                    'is_in_stock' => $wishlistItem->product->in_stock ?? ($wishlistItem->product->stock_status === 'in_stock'),
                ],
                'added_at' => $wishlistItem->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Remove product from wishlist.
     */
    public function remove(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get customer record
        $customer = \Vortex\Customer\Models\Customer::where('user_id', $user->id)->first();
        
        if (!$customer) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $wishlistItem = Wishlist::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$wishlistItem) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $wishlistItem->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }

    /**
     * Move wishlist item to cart.
     */
    public function moveToCart(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get or create customer record
        $customer = \Vortex\Customer\Models\Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->name ?? 'Customer',
                'email' => $user->email,
                'is_active' => true,
            ]
        );

        $wishlistItem = Wishlist::where('customer_id', $customer->id)
            ->where('id', $id)
            ->with('product.mainImage')
            ->first();

        if (!$wishlistItem) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $product = $wishlistItem->product;

        // Check if product is in stock
        if (!($product->in_stock ?? ($product->stock_status === 'in_stock'))) {
            return response()->json(['message' => 'Product is out of stock'], 400);
        }

        // Get existing cart from session
        $cart = Session::get('cart', []);
        
        // Check if product already in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $product->id) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }
        
        // Add new item if not found
        if (!$found) {
            $cart[] = [
                'id' => uniqid('cart_'),
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->special_price ?? $product->price,
                'quantity' => 1,
                'image' => $product->mainImage?->url ?? $product->images->first()?->url,
                'tax_class_id' => $product->tax_class_id,
                'weight' => $product->weight,
            ];
        }
        
        // Save cart to session
        Session::put('cart', $cart);

        // Remove from wishlist
        $wishlistItem->delete();

        return response()->json([
            'message' => 'Moved to cart successfully',
            'cart_count' => collect($cart)->sum('quantity'),
        ]);
    }
}
