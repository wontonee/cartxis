<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vortex\Product\Models\Product;
use Vortex\Product\Models\Category;
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

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'category_id', 'stock_status', 'sort_by', 'sort_order']),
            'categories' => Category::enabled()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::enabled()->with('children')->whereNull('parent_id')->orderBy('name')->get(),
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $product = Product::create($validated);

        if (!empty($categoryIds)) {
            $product->categories()->attach($categoryIds);
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

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::enabled()->with('children')->whereNull('parent_id')->orderBy('name')->get(),
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $product->update($validated);

        $product->categories()->sync($categoryIds);

        // Flash the message to session
        session()->flash('success', 'Product updated successfully!');

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
}
