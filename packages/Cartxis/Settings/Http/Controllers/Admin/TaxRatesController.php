<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Models\TaxRate;

class TaxRatesController
{
    public function index(): Response
    {
        $taxRates = TaxRate::orderBy('priority')->orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Index', [
            'taxRates' => $taxRates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_rates,code',
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'priority' => 'integer|min:0',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
        ]);

        TaxRate::create($validated);

        return redirect()->back()->with('success', 'Tax rate created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $taxRate = TaxRate::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_rates,code,' . $id,
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'priority' => 'integer|min:0',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $taxRate->update($validated);

        return redirect()->back()->with('success', 'Tax rate updated successfully.');
    }

    public function destroy(int $id)
    {
        $taxRate = TaxRate::findOrFail($id);

        // Check if tax rate is used in tax rules
        if ($taxRate->rules()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tax rate that is used in tax rules.');
        }

        $taxRate->delete();

        return redirect()->back()->with('success', 'Tax rate deleted successfully.');
    }
}
