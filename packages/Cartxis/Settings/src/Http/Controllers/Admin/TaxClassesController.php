<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Models\TaxClass;

class TaxClassesController
{
    public function index(): Response
    {
        $taxClasses = TaxClass::orderBy('name')->get();

        return Inertia::render('Admin/Settings/TaxRules/Index', [
            'taxClasses' => $taxClasses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_classes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        TaxClass::create($validated);

        return redirect()->back()->with('success', 'Tax class created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $taxClass = TaxClass::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:tax_classes,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $taxClass->update($validated);

        return redirect()->back()->with('success', 'Tax class updated successfully.');
    }

    public function destroy(int $id)
    {
        $taxClass = TaxClass::findOrFail($id);

        // Check if tax class is used in tax rules
        if ($taxClass->rules()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tax class that is used in tax rules.');
        }

        $taxClass->delete();

        return redirect()->back()->with('success', 'Tax class deleted successfully.');
    }
}
