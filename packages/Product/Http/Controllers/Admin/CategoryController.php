<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Product\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $query = Category::query()->with('parent');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by parent
        if ($request->has('parent_id') && $request->parent_id !== '') {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'position');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $categories = $query->paginate($request->get('per_page', 15))->withQueryString();

        return Inertia::render('admin/catalog/categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'parent_id', 'sort_by', 'sort_order']),
            'parentCategories' => Category::whereNull('parent_id')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('admin/catalog/categories/Create', [
            'parentCategories' => Category::whereNull('parent_id')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'position' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Auto-set position if not provided
        if (empty($validated['position'])) {
            $maxPosition = Category::where('parent_id', $validated['parent_id'] ?? null)->max('position') ?? 0;
            $validated['position'] = $maxPosition + 1;
        }

        Category::create($validated);

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): Response
    {
        $category->load('parent', 'children', 'products');

        return Inertia::render('admin/catalog/categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('admin/catalog/categories/Edit', [
            'category' => $category,
            'parentCategories' => Category::whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'position' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Prevent setting itself as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => 'Category cannot be its own parent.']);
        }

        $category->update($validated);

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with sub-categories.']);
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with products.']);
        }

        $category->delete();

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            $category = Category::find($id);
            
            if ($category->children()->count() > 0) {
                $errors[] = "Category '{$category->name}' has sub-categories.";
                continue;
            }

            if ($category->products()->count() > 0) {
                $errors[] = "Category '{$category->name}' has products.";
                continue;
            }

            $category->delete();
            $deleted++;
        }

        $message = "{$deleted} categories deleted successfully.";
        
        if (count($errors) > 0) {
            $message .= ' ' . implode(' ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk update status.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'status' => 'required|boolean',
        ]);

        Category::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return back()->with('success', count($request->ids) . ' categories status updated successfully.');
    }
}
