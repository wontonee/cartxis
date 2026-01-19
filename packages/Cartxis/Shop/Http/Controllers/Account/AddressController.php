<?php

namespace Cartxis\Shop\Http\Controllers\Account;

use Illuminate\Http\Request;
use Cartxis\Shop\Http\Controllers\Controller;
use Cartxis\Shop\Models\Address;
use Cartxis\Core\Services\ThemeViewResolver;

class AddressController extends Controller
{
    protected ThemeViewResolver $themeResolver;

    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display a listing of the user's addresses.
     */
    public function index()
    {
        $addresses = Address::where('addressable_type', \App\Models\User::class)
            ->where('addressable_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return $this->themeResolver->inertia('Account/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean',
            'address_type' => 'required|in:shipping,billing',
        ]);

        // If this is set as default, unset other defaults of the same type
        if ($validated['is_default'] ?? false) {
            Address::where('addressable_type', \App\Models\User::class)
                ->where('addressable_id', auth()->id())
                ->where('type', $validated['address_type'])
                ->update(['is_default' => false]);
        }

        $address = Address::create([
            'addressable_type' => \App\Models\User::class,
            'addressable_id' => auth()->id(),
            'type' => $validated['address_type'],
            'label' => $validated['label'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'company' => $validated['company'] ?? null,
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'phone' => $validated['phone'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return back()->with('success', 'Address added successfully.');
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address)
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_type !== \App\Models\User::class || $address->addressable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean',
            'address_type' => 'required|in:shipping,billing',
        ]);

        // If this is set as default, unset other defaults of the same type
        if ($validated['is_default'] ?? false) {
            Address::where('addressable_type', \App\Models\User::class)
                ->where('addressable_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->where('type', $validated['address_type'])
                ->update(['is_default' => false]);
        }

        $address->update([
            'type' => $validated['address_type'],
            'label' => $validated['label'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'company' => $validated['company'] ?? null,
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'phone' => $validated['phone'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return back()->with('success', 'Address updated successfully.');
    }

    /**
     * Set an address as default.
     */
    public function setDefault(Address $address)
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_type !== \App\Models\User::class || $address->addressable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Unset other defaults of the same type
        Address::where('addressable_type', \App\Models\User::class)
            ->where('addressable_id', auth()->id())
            ->where('id', '!=', $address->id)
            ->where('type', $address->type)
            ->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated successfully.');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_type !== \App\Models\User::class || $address->addressable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Prevent deleting the last address
        $addressCount = Address::where('addressable_type', \App\Models\User::class)
            ->where('addressable_id', auth()->id())
            ->count();

        if ($addressCount <= 1) {
            return back()->with('error', 'Cannot delete your last address. Please add another address first.');
        }

        // If deleting the default address, set another address as default
        if ($address->is_default) {
            $newDefault = Address::where('addressable_type', \App\Models\User::class)
                ->where('addressable_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->where('type', $address->type)
                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully.');
    }
}
