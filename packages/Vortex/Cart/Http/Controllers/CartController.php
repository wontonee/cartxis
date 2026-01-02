<?php

namespace Vortex\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Vortex\Core\Services\ThemeViewResolver;
use Vortex\Cart\Services\CartTaxCalculator;
use Vortex\Cart\Services\CartShippingCalculator;
use Vortex\Product\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * @var ThemeViewResolver
     */
    protected $themeResolver;

    /**
     * Create a new controller instance.
     *
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display the shopping cart page
     */
    public function index(): Response
    {
        // Get cart items from session
        $items = $this->getCartItems();
        
        // Calculate subtotal
        $subtotal = collect($items)->sum(fn($item) => $item['price'] * $item['quantity']);
        
        // Customer address (TODO: get from user profile or session)
        $customerAddress = [];
        
        // Calculate taxes
        $taxCalculator = new CartTaxCalculator();
        $taxResult = $taxCalculator->calculate($items, $customerAddress);
        
        // Calculate shipping
        $shippingCalculator = new CartShippingCalculator();
        $shippingResult = $shippingCalculator->calculate($items, $customerAddress);
        
        // Get default shipping (cheapest option)
        $selectedShipping = $shippingResult['default'] ?? null;
        $shippingCost = $selectedShipping['cost'] ?? 0;
        
        // Calculate grand total
        $taxTotal = $taxResult['total'];
        $grandTotal = $subtotal + $taxTotal + $shippingCost;
        
        return Inertia::render($this->themeResolver->resolve('Cart/Index'), [
            'pageTitle' => 'Shopping Cart',
            'cartSummary' => [
                'subtotal' => round($subtotal, 2),
                'taxes' => [
                    'breakdown' => $taxResult['breakdown'],
                    'total' => round($taxTotal, 2),
                ],
                'shipping' => [
                    'options' => $shippingResult['options'],
                    'selected' => $selectedShipping,
                    'cost' => round($shippingCost, 2),
                ],
                'total' => round($grandTotal, 2),
            ],
        ]);
    }
    
    /**
     * Get cart items from session
     */
    private function getCartItems(): array
    {
        $items = Session::get('cart', []);
        
        // Enrich items with product data if needed
        foreach ($items as &$item) {
            if (!isset($item['tax_class_id']) || !isset($item['weight'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $item['tax_class_id'] = $product->tax_class_id;
                    $item['weight'] = $product->weight;
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $product = Product::with('mainImage')->find($request->product_id);
        
        if (!$product || $product->status !== 'enabled') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Product not available'], 400);
            }
            return back()->with('error', 'Product not available');
        }
        
        // Check stock
        if ($product->track_inventory && $product->quantity < $request->quantity) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient stock available'], 400);
            }
            return back()->with('error', 'Insufficient stock');
        }
        
        // Get existing cart
        $cart = Session::get('cart', []);
        
        // Check if product already in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $request->product_id) {
                $item['quantity'] += $request->quantity;
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
                'quantity' => $request->quantity,
                'image' => $product->mainImage?->url,
                'tax_class_id' => $product->tax_class_id,
                'weight' => $product->weight,
            ];
        }
        
        Session::put('cart', $cart);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product added to cart successfully',
                'items' => $cart,
                'count' => collect($cart)->sum('quantity'),
                'subtotal' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            ]);
        }
        
        return back()->with('success', 'Product added to cart');
    }
    
    /**
     * Update cart item quantity
     */
    public function update(Request $request, $index)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$index])) {
            $cart[$index]['quantity'] = $request->quantity;
            Session::put('cart', $cart);
            return back()->with('success', 'Cart updated');
        }
        
        return back()->with('error', 'Item not found');
    }
    
    /**
     * Remove item from cart
     */
    public function remove($index)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // Reindex array
            Session::put('cart', $cart);
            return back()->with('success', 'Item removed from cart');
        }
        
        return back()->with('error', 'Item not found');
    }
    
    /**
     * Clear all cart items
     */
    public function clear()
    {
        Session::forget('cart');
        return back()->with('success', 'Cart cleared');
    }
}
