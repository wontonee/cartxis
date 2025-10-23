<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Product\Models\Attribute;
use Vortex\Product\Models\AttributeOption;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     */
    public function index(Request $request): Response
    {
        $query = Attribute::query()->withCount('options');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $attributes = $query->paginate($request->get('per_page', 15))->withQueryString();

        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => $attributes,
            'filters' => $request->only(['search', 'type', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Attributes/Create', [
            //
        ]);
    }

    /**
     * Store a newly created attribute.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:attributes,code',
            'type' => 'required|in:text,textarea,select,multiselect,boolean,date,price',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_configurable' => 'boolean',
            'sort_order' => 'nullable|integer',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string|max:255',
            'options.*.value' => 'required_with:options|string|max:255',
            'options.*.swatch_value' => 'nullable|string|max:255',
            'options.*.sort_order' => 'nullable|integer',
        ]);

        // Auto-set sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $maxSortOrder = Attribute::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxSortOrder + 1;
        }

        $options = $validated['options'] ?? [];
        unset($validated['options']);

        $attribute = Attribute::create($validated);

        // Create options if type is select or multiselect
        if (in_array($validated['type'], ['select', 'multiselect']) && !empty($options)) {
            foreach ($options as $option) {
                AttributeOption::create([
                    'attribute_id' => $attribute->id,
                    'label' => $option['label'],
                    'value' => $option['value'],
                    'swatch_value' => $option['swatch_value'] ?? null,
                    'sort_order' => $option['sort_order'] ?? 0,
                ]);
            }
        }

        return redirect()->route('admin.catalog.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified attribute.
     */
    public function show(Attribute $attribute): Response
    {
        $attribute->load('options');

        return Inertia::render('Admin/Attributes/Show', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Attribute $attribute): Response
    {
        $attribute->load('options');

        return Inertia::render('Admin/Attributes/Edit', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Update the specified attribute.
     */
    public function update(Request $request, Attribute $attribute): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:attributes,code,' . $attribute->id,
            'type' => 'required|in:text,textarea,select,multiselect,boolean,date,price,number',
            'is_required' => 'boolean',
            'is_unique' => 'boolean',
            'is_filterable' => 'boolean',
            'is_comparable' => 'boolean',
            'is_visible_on_front' => 'boolean',
            'position' => 'nullable|integer',
            'validation' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.id' => 'nullable|exists:attribute_options,id',
            'options.*.label' => 'required|string|max:255',
            'options.*.value' => 'required|string|max:255',
            'options.*.position' => 'nullable|integer',
        ]);

        $options = $validated['options'] ?? [];
        unset($validated['options']);

        $attribute->update($validated);

        // Update or create options
        if (in_array($validated['type'], ['select', 'multiselect'])) {
            $existingOptionIds = [];

            foreach ($options as $index => $optionData) {
                if (!empty($optionData['id'])) {
                    // Update existing option
                    $option = AttributeOption::find($optionData['id']);
                    if ($option && $option->attribute_id == $attribute->id) {
                        $option->update([
                            'label' => $optionData['label'],
                            'value' => $optionData['value'],
                            'position' => $optionData['position'] ?? $index,
                        ]);
                        $existingOptionIds[] = $option->id;
                    }
                } else {
                    // Create new option
                    $newOption = AttributeOption::create([
                        'attribute_id' => $attribute->id,
                        'label' => $optionData['label'],
                        'value' => $optionData['value'],
                        'position' => $optionData['position'] ?? $index,
                    ]);
                    $existingOptionIds[] = $newOption->id;
                }
            }

            // Delete options that were removed
            AttributeOption::where('attribute_id', $attribute->id)
                ->whereNotIn('id', $existingOptionIds)
                ->delete();
        }

        return redirect()->route('admin.catalog.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(Attribute $attribute): RedirectResponse
    {
        try {
            // Delete associated options and attribute
            $attribute->options()->delete();
            $attribute->delete();

            return back()->with('success', 'Attribute deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete attribute: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete attributes.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attributes,id',
        ]);

        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $attribute = Attribute::find($id);
                if ($attribute) {
                    $attribute->options()->delete();
                    $attribute->delete();
                    $count++;
                }
            }

            return back()->with('success', $count . ' attribute(s) deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete attributes: ' . $e->getMessage()]);
        }
    }
}
