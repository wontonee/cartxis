<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Product\Models\Brand;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index(Request $request): Response
    {
        $query = Brand::query()->withCount('products');

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by featured
        if ($request->has('is_featured') && $request->is_featured !== '') {
            $query->where('is_featured', $request->is_featured);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $brands = $query->paginate($request->get('per_page', 15))->withQueryString();

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
            'filters' => $request->only(['search', 'status', 'is_featured', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Brands/Create', [
            //
        ]);
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'website' => 'nullable|url|max:255',
            'status' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ], [
            'name.required' => 'Brand name is required',
            'name.max' => 'Brand name must not exceed 255 characters',
            'slug.unique' => 'This slug is already in use',
            'logo.image' => 'Logo must be an image file',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file',
            'logo.max' => 'Logo file size must not exceed 5MB',
            'website.url' => 'Please enter a valid URL',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
            $validated['logo'] = $logoPath;
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Auto-set sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $maxSortOrder = Brand::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxSortOrder + 1;
        }

        // Convert boolean fields properly
        $validated['status'] = filter_var($request->input('status', false), FILTER_VALIDATE_BOOLEAN);
        $validated['is_featured'] = filter_var($request->input('is_featured', false), FILTER_VALIDATE_BOOLEAN);

        Brand::create($validated);

        return redirect()->route('admin.catalog.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand): Response
    {
        $brand->load('products');
        $brand->loadCount('products');

        return Inertia::render('Admin/Brands/Show', [
            'brand' => $brand,
        ]);
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): Response
    {
        return Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'website' => 'nullable|url|max:255',
            'status' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ], [
            'name.required' => 'Brand name is required',
            'name.max' => 'Brand name must not exceed 255 characters',
            'slug.unique' => 'This slug is already in use',
            'logo.image' => 'Logo must be an image file',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file',
            'logo.max' => 'Logo file size must not exceed 5MB',
            'website.url' => 'Please enter a valid URL',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo && \Storage::disk('public')->exists($brand->logo)) {
                \Storage::disk('public')->delete($brand->logo);
            }
            
            $logoPath = $request->file('logo')->store('brands', 'public');
            $validated['logo'] = $logoPath;
        } else {
            // Keep existing logo
            unset($validated['logo']);
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Convert boolean fields properly
        $validated['status'] = filter_var($request->input('status', false), FILTER_VALIDATE_BOOLEAN);
        $validated['is_featured'] = filter_var($request->input('is_featured', false), FILTER_VALIDATE_BOOLEAN);

        $brand->update($validated);

        return back()->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        // Check if brand has products
        if ($brand->hasProducts()) {
            return back()->withErrors(['error' => 'Cannot delete brand with associated products.']);
        }

        try {
            $brand->delete();
            return back()->with('success', 'Brand deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete brand: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
        ]);

        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $brand = Brand::find($id);
                if ($brand && !$brand->hasProducts()) {
                    $brand->delete();
                    $count++;
                }
            }

            if ($count > 0) {
                return back()->with('success', $count . ' brand(s) deleted successfully.');
            } else {
                return back()->withErrors(['error' => 'Cannot delete brands with associated products.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete brands: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk update brand status.
     */
    public function bulkStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
            'status' => 'required|boolean',
        ]);

        Brand::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return back()->with('success', count($request->ids) . ' brand(s) status updated successfully.');
    }
}
