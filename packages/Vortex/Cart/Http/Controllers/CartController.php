<?php

namespace Vortex\Cart\Http\Controllers;

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
}
