<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Models\Country;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CountryController extends Controller
{
    /**
     * List all countries.
     */
    public function index(Request $request)
    {
        $query = Country::query()->ordered();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $countries = $query->paginate(50)->withQueryString();

        return Inertia::render('Admin/Settings/Countries/Index', [
            'countries' => $countries,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store a new country.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|size:2|unique:countries,code',
            'code3' => 'nullable|string|size:3',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        Country::create($validated);

        return back()->with('success', 'Country created successfully.');
    }

    /**
     * Update an existing country.
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|size:2|unique:countries,code,' . $country->id,
            'code3' => 'nullable|string|size:3',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $country->update($validated);

        return back()->with('success', 'Country updated successfully.');
    }

    /**
     * Toggle country active status.
     */
    public function toggle(Country $country)
    {
        $country->update(['is_active' => !$country->is_active]);

        return back()->with('success', "Country {$country->name} " . ($country->is_active ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Bulk toggle status.
     */
    public function bulkToggle(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:countries,id',
            'is_active' => 'required|boolean',
        ]);

        Country::whereIn('id', $validated['ids'])
            ->update(['is_active' => $validated['is_active']]);

        return back()->with('success', count($validated['ids']) . ' countries updated.');
    }

    /**
     * Delete a country.
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return back()->with('success', 'Country deleted successfully.');
    }

    /**
     * API: Return active countries (for frontend dropdowns).
     */
    public function activeList()
    {
        $countries = Country::active()
            ->ordered()
            ->select('id', 'name', 'code', 'phone_code', 'currency_code', 'currency_symbol')
            ->get();

        return response()->json($countries);
    }
}
