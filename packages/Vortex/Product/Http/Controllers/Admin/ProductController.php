<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vortex\Product\Models\Product;
use Vortex\Product\Models\Category;
use Vortex\Product\Models\InventoryAdjustment;
use Vortex\Core\Models\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): Response
    {
        $query = Product::query()
            ->with(['categories', 'mainImage'])
            ->withCount('images');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        // Filter by stock status
        if ($stockStatus = $request->input('stock_status')) {
            $query->where('stock_status', $stockStatus);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(15)->withQueryString();

        // Get default currency
        $currency = Currency::getDefault();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'category_id', 'stock_status', 'sort_by', 'sort_order']),
            'categories' => Category::enabled()->orderBy('name')->get(['id', 'name']),
            'currency' => $currency ? [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'symbol_position' => $currency->symbol_position,
                'decimal_places' => $currency->decimal_places,
            ] : [
                'code' => 'USD',
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_places' => 2,
            ],
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::enabled()->with('children')->whereNull('parent_id')->orderBy('name')->get(),
            'brands' => \Vortex\Product\Models\Brand::where('status', true)->orderBy('name')->get(['id', 'name']),
            'taxClasses' => \Vortex\Core\Models\TaxClass::orderBy('name')->get(['id', 'name', 'code']),
            'attributes' => \Vortex\Product\Models\Attribute::with('options')
                ->where(function($query) {
                    $query->where('is_filterable', true)
                          ->orWhere('is_configurable', true);
                })
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code', 'type', 'is_required', 'is_filterable', 'is_configurable']),
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'slug' => 'nullable|string|unique:products,slug',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'special_price_from' => 'nullable|date',
            'special_price_to' => 'nullable|date|after_or_equal:special_price_from',
            'status' => 'required|in:enabled,disabled',
            'visibility' => 'required|in:catalog,search,both,none',
            'featured' => 'boolean',
            'new' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,on_backorder',
            'manage_stock' => 'boolean',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'type' => 'required|in:simple,configurable,virtual,downloadable',
            'weight' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'attribute_values' => 'nullable|array',
        ]);

        $categoryIds = $validated['category_ids'] ?? [];
        $attributeValues = $validated['attribute_values'] ?? [];
        unset($validated['category_ids'], $validated['attribute_values']);

        $product = Product::create($validated);

        if (!empty($categoryIds)) {
            $product->categories()->attach($categoryIds);
        }

        // Save attribute values
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $code => $value) {
                $attribute = \Vortex\Product\Models\Attribute::where('code', $code)->first();
                if ($attribute) {
                    // Convert arrays to JSON for multiselect
                    $valueToStore = is_array($value) ? json_encode($value) : $value;
                    
                    \Vortex\Product\Models\ProductAttributeValue::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'attribute_id' => $attribute->id,
                        ],
                        [
                            'value' => $valueToStore,
                        ]
                    );
                }
            }
        }

        // Flash the message to session
        session()->flash('success', 'Product created successfully!');

        return redirect()->route('admin.catalog.products.index');
    }

    /**
     * Show the form for editing the product
     */
    public function edit(Product $product): Response
    {
        $product->load(['categories', 'images', 'attributeValues.attribute', 'attributeValues.option']);

        // Load recent inventory adjustments
        $adjustmentHistory = InventoryAdjustment::where('product_id', $product->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get default currency
        $currency = Currency::getDefault();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::enabled()->with('children')->whereNull('parent_id')->orderBy('name')->get(),
            'brands' => \Vortex\Product\Models\Brand::where('status', true)->orderBy('name')->get(['id', 'name']),
            'taxClasses' => \Vortex\Core\Models\TaxClass::orderBy('name')->get(['id', 'name', 'code']),
            'attributes' => \Vortex\Product\Models\Attribute::with('options')
                ->where(function($query) {
                    $query->where('is_filterable', true)
                          ->orWhere('is_configurable', true);
                })
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code', 'type', 'is_required', 'is_filterable', 'is_configurable']),
            'adjustmentHistory' => $adjustmentHistory,
            'currency' => $currency ? [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'symbol_position' => $currency->symbol_position,
                'decimal_places' => $currency->decimal_places,
            ] : [
                'code' => 'USD',
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_places' => 2,
            ],
        ]);
    }

    /**
     * Update the product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'special_price_from' => 'nullable|date',
            'special_price_to' => 'nullable|date|after_or_equal:special_price_from',
            'status' => 'required|in:enabled,disabled',
            'visibility' => 'required|in:catalog,search,both,none',
            'featured' => 'boolean',
            'new' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,on_backorder',
            'manage_stock' => 'boolean',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'type' => 'required|in:simple,configurable,virtual,downloadable',
            'weight' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'attribute_values' => 'nullable|array',
        ]);

        $categoryIds = $validated['category_ids'] ?? [];
        $attributeValues = $validated['attribute_values'] ?? [];
        unset($validated['category_ids'], $validated['attribute_values']);

        // Track inventory changes
        $oldQuantity = $product->quantity;
        $newQuantity = $validated['quantity'];

        $product->update($validated);

        $product->categories()->sync($categoryIds);

        // Update attribute values
        // First, get all current attribute IDs for this product
        $currentAttributeCodes = array_keys($attributeValues);
        $currentAttributeIds = \Vortex\Product\Models\Attribute::whereIn('code', $currentAttributeCodes)->pluck('id')->toArray();
        
        // Delete attribute values that are no longer selected
        \Vortex\Product\Models\ProductAttributeValue::where('product_id', $product->id)
            ->whereNotIn('attribute_id', $currentAttributeIds)
            ->delete();
        
        // Update or create attribute values
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $code => $value) {
                $attribute = \Vortex\Product\Models\Attribute::with('options')->where('code', $code)->first();
                if ($attribute) {
                    // Handle different attribute types
                    if ($attribute->type === 'multiselect') {
                        // For multiselect, delete existing values first
                        \Vortex\Product\Models\ProductAttributeValue::where('product_id', $product->id)
                            ->where('attribute_id', $attribute->id)
                            ->delete();
                        
                        // Create a new row for each selected option
                        $values = is_array($value) ? $value : [$value];
                        foreach ($values as $optionValue) {
                            if (!empty($optionValue)) {
                                // Find the option by value
                                $option = $attribute->options->firstWhere('value', $optionValue);
                                if ($option) {
                                    \Vortex\Product\Models\ProductAttributeValue::create([
                                        'product_id' => $product->id,
                                        'attribute_id' => $attribute->id,
                                        'attribute_option_id' => $option->id,
                                    ]);
                                }
                            }
                        }
                    } elseif ($attribute->type === 'select') {
                        // For single select
                        $option = $attribute->options->firstWhere('value', $value);
                        \Vortex\Product\Models\ProductAttributeValue::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                            ],
                            [
                                'attribute_option_id' => $option ? $option->id : null,
                            ]
                        );
                    } elseif ($attribute->type === 'boolean') {
                        // For boolean
                        \Vortex\Product\Models\ProductAttributeValue::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                            ],
                            [
                                'boolean_value' => $value ? 1 : 0,
                            ]
                        );
                    } elseif ($attribute->type === 'date') {
                        // For date
                        \Vortex\Product\Models\ProductAttributeValue::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                            ],
                            [
                                'date_value' => $value,
                            ]
                        );
                    } else {
                        // For text/textarea
                        \Vortex\Product\Models\ProductAttributeValue::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                            ],
                            [
                                'text_value' => $value,
                            ]
                        );
                    }
                }
            }
        } else {
            // If no attribute values provided, delete all existing ones
            \Vortex\Product\Models\ProductAttributeValue::where('product_id', $product->id)->delete();
        }

        // Record inventory adjustment if quantity changed
        if ($oldQuantity != $newQuantity && auth()->check()) {
            $this->recordInventoryAdjustment($product, $oldQuantity, $newQuantity, $request);
        }

        // Flash the message to session
        session()->flash('success', 'Product updated successfully!');

        // If it's an AJAX/Inertia request, return back to stay on same page
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return redirect()->back();
        }

        return redirect()->route('admin.catalog.products.index');
    }

    /**
     * Remove the product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk delete products
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        Product::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('success', count($request->ids) . ' products deleted successfully!');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'status' => 'required|in:enabled,disabled',
        ]);

        Product::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('success', count($request->ids) . ' products updated successfully!');
    }

    /**
     * Record inventory adjustment
     */
    protected function recordInventoryAdjustment(Product $product, int $oldQuantity, int $newQuantity, Request $request)
    {
        // Determine adjustment type
        if ($newQuantity > $oldQuantity) {
            $type = 'addition';
            $quantityAdjusted = $newQuantity - $oldQuantity;
        } elseif ($newQuantity < $oldQuantity) {
            $type = 'subtraction';
            $quantityAdjusted = $oldQuantity - $newQuantity;
        } else {
            return; // No change
        }

        // Get reason from request or use default
        $reason = $request->input('adjustment_reason', 'Stock adjustment via product edit');
        $notes = $request->input('adjustment_notes');

        InventoryAdjustment::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'quantity_adjusted' => $quantityAdjusted,
            'reason' => $reason,
            'notes' => $notes,
            'user_id' => $request->user()->id,
        ]);
    }
}
