<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\TaxZone;

class TaxZonesController
{
    public function index(): Response
    {
        $taxZones = TaxZone::with('locations')->orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Index', [
            'taxZones' => $taxZones,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_zones,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'locations' => 'array',
            'locations.*.country_code' => 'required|string|size:2',
            'locations.*.state_code' => 'nullable|string|max:100',
            'locations.*.postal_code_pattern' => 'nullable|string|max:255',
            'locations.*.city' => 'nullable|string|max:255',
        ]);

        $taxZone = TaxZone::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['locations'])) {
            foreach ($validated['locations'] as $location) {
                $taxZone->locations()->create($location);
            }
        }

        return redirect()->back()->with('success', 'Tax zone created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $taxZone = TaxZone::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_zones,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'locations' => 'array',
            'locations.*.country_code' => 'required|string|size:2',
            'locations.*.state_code' => 'nullable|string|max:100',
            'locations.*.postal_code_pattern' => 'nullable|string|max:255',
            'locations.*.city' => 'nullable|string|max:255',
        ]);

        $taxZone->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update locations: delete all and recreate
        if (isset($validated['locations'])) {
            $taxZone->locations()->delete();
            foreach ($validated['locations'] as $location) {
                $taxZone->locations()->create($location);
            }
        }

        return redirect()->back()->with('success', 'Tax zone updated successfully.');
    }

    public function destroy(int $id)
    {
        $taxZone = TaxZone::findOrFail($id);

        // Check if tax zone is used in tax rules
        if ($taxZone->rules()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tax zone that is used in tax rules.');
        }

        $taxZone->delete();

        return redirect()->back()->with('success', 'Tax zone deleted successfully.');
    }
}
