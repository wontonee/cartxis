<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\TaxRule;
use Vortex\Core\Models\TaxClass;
use Vortex\Core\Models\TaxRate;
use Vortex\Core\Models\TaxZone;

class TaxRulesController
{
    public function index(Request $request): Response
    {
        $query = TaxRule::with(['taxClass', 'taxZone', 'taxRate']);

        // Apply filters
        if ($request->filled('tax_class_id')) {
            $query->where('tax_class_id', $request->tax_class_id);
        }

        if ($request->filled('tax_zone_id')) {
            $query->where('tax_zone_id', $request->tax_zone_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Apply search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply sorting
        $sortField = $request->get('sort', 'priority');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $taxRules = $query->paginate(15);

        // Get all data for tabs
        $taxClasses = TaxClass::orderBy('name')->get();
        $taxRates = TaxRate::orderBy('priority')->orderBy('name')->get();
        $taxZones = TaxZone::with('locations')->orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Index', [
            'taxRules' => $taxRules,
            'taxClasses' => $taxClasses,
            'taxRates' => $taxRates,
            'taxZones' => $taxZones,
            'filters' => $request->only(['search', 'tax_class_id', 'tax_zone_id', 'status', 'sort', 'direction']),
        ]);
    }

    public function create(): Response
    {
        $taxClasses = TaxClass::orderBy('name')->get();
        $taxZones = TaxZone::active()->orderBy('name')->get();
        $taxRates = TaxRate::active()->orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Create', [
            'taxClasses' => $taxClasses,
            'taxZones' => $taxZones,
            'taxRates' => $taxRates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_class_id' => 'required|exists:tax_classes,id',
            'tax_zone_id' => 'required|exists:tax_zones,id',
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'priority' => 'integer|min:0',
            'calculate_shipping' => 'boolean',
            'is_active' => 'boolean',
        ]);

        TaxRule::create($validated);

        return redirect()->route('admin.settings.tax-rules.index')
            ->with('success', 'Tax rule created successfully.');
    }

    public function edit(int $id): Response
    {
        $taxRule = TaxRule::with(['taxClass', 'taxZone', 'taxRate'])->findOrFail($id);
        $taxClasses = TaxClass::orderBy('name')->get();
        $taxZones = TaxZone::active()->orderBy('name')->get();
        $taxRates = TaxRate::active()->orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Edit', [
            'taxRule' => $taxRule,
            'taxClasses' => $taxClasses,
            'taxZones' => $taxZones,
            'taxRates' => $taxRates,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $taxRule = TaxRule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_class_id' => 'required|exists:tax_classes,id',
            'tax_zone_id' => 'required|exists:tax_zones,id',
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'priority' => 'integer|min:0',
            'calculate_shipping' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $taxRule->update($validated);

        return redirect()->route('admin.settings.tax-rules.index')
            ->with('success', 'Tax rule updated successfully.');
    }

    public function destroy(int $id)
    {
        $taxRule = TaxRule::findOrFail($id);
        $taxRule->delete();

        return redirect()->back()->with('success', 'Tax rule deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tax_rules,id',
        ]);

        TaxRule::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' tax rules deleted successfully.');
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tax_rules,id',
            'status' => 'required|boolean',
        ]);

        TaxRule::whereIn('id', $request->ids)->update(['is_active' => $request->status]);

        $statusText = $request->status ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', count($request->ids) . ' tax rules ' . $statusText . ' successfully.');
    }
}
