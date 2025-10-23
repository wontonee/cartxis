<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Vortex\Core\Models\Locale;
use Vortex\Core\Models\Currency;

class LocalesController extends Controller
{
    /**
     * Display a listing of locales and currencies.
     */
    public function index()
    {
        $locales = Locale::orderBy('sort_order')->orderBy('name')->get();
        $currencies = Currency::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/Settings/Locales/Index', [
            'locales' => $locales,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Store a newly created locale.
     */
    public function storeLocale(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:locales,code|alpha_dash',
            'name' => 'required|string|max:100|min:2',
            'native_name' => 'nullable|string|max:100|min:2',
            'direction' => 'required|in:ltr,rtl',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $locale = Locale::create($validated);

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Locale created successfully.');
    }

    /**
     * Update the specified locale.
     */
    public function updateLocale(Request $request, Locale $locale)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:locales,code,' . $locale->id . '|alpha_dash',
            'name' => 'required|string|max:100|min:2',
            'native_name' => 'nullable|string|max:100|min:2',
            'direction' => 'required|in:ltr,rtl',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $locale->update($validated);

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Locale updated successfully.');
    }

    /**
     * Remove the specified locale.
     */
    public function destroyLocale(Locale $locale)
    {
        // Prevent deletion of default locale if it's the only active one
        if ($locale->is_default && Locale::where('is_active', true)->count() === 1) {
            return redirect()->route('admin.settings.locales.index')
                ->with('error', 'Cannot delete the only active locale.');
        }

        $locale->delete();

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Locale deleted successfully.');
    }

    /**
     * Store a newly created currency.
     */
    public function storeCurrency(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code|alpha',
            'name' => 'required|string|max:100|min:2',
            'symbol' => 'required|string|max:10',
            'symbol_position' => 'required|in:before,after',
            'decimal_places' => 'required|integer|min:0|max:10',
            'exchange_rate' => 'required|numeric|min:0.0000000001|max:9999999999',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $currency = Currency::create($validated);

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Currency created successfully.');
    }

    /**
     * Update the specified currency.
     */
    public function updateCurrency(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,' . $currency->id . '|alpha',
            'name' => 'required|string|max:100|min:2',
            'symbol' => 'required|string|max:10',
            'symbol_position' => 'required|in:before,after',
            'decimal_places' => 'required|integer|min:0|max:10',
            'exchange_rate' => 'required|numeric|min:0.0000000001|max:9999999999',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $currency->update($validated);

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Currency updated successfully.');
    }

    /**
     * Remove the specified currency.
     */
    public function destroyCurrency($id)
    {
        $currency = Currency::findOrFail($id);

        // Prevent deletion of default currency if it's the only active one
        if ($currency->is_default && Currency::where('is_active', true)->count() === 1) {
            return redirect()->route('admin.settings.locales.index')
                ->with('error', 'Cannot delete the only active currency.');
        }

        $currency->delete();

        return redirect()->route('admin.settings.locales.index')
            ->with('success', 'Currency deleted successfully.');
    }
}
