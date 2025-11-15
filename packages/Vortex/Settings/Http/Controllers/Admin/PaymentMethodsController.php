<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Settings\Http\Requests\SavePaymentMethodRequest;

class PaymentMethodsController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index(): Response
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();

        return Inertia::render('Admin/Settings/PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Get configuration form for a specific payment method type.
     */
    public function getConfigure(string $type): Response
    {
        // Try to find by code first (for custom gateways like razorpay), then by type
        $method = PaymentMethod::where('code', $type)
            ->orWhere('type', $type)
            ->first();

        if (!$method) {
            abort(404, 'Payment method not found');
        }

        return Inertia::render("Admin/Settings/PaymentMethods/Configure{$this->getComponentName($method->code)}", [
            'method' => $method,
        ]);
    }

    /**
     * Save payment method configuration.
     */
    public function save(SavePaymentMethodRequest $request, string $type)
    {
        // Try to find by code first (for custom gateways), then by type
        $method = PaymentMethod::where('code', $type)
            ->orWhere('type', $type)
            ->firstOrFail();

        // Update method with validated data
        $method->update($request->validated());

        return back()->with('success', "Payment method '{$method->name}' updated successfully.");
    }

    /**
     * Toggle payment method active status.
     */
    public function toggle(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update([
            'is_active' => !$paymentMethod->is_active,
        ]);

        return back()->with('success', 'Payment method status updated.');
    }

    /**
     * Set payment method as default.
     */
    public function setDefault(PaymentMethod $paymentMethod)
    {
        PaymentMethod::where('is_default', true)->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return back()->with('success', 'Default payment method updated.');
    }

    /**
     * Update sort order.
     */
    public function sort(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'sort_order' => 'required|integer|min:0',
        ]);

        $paymentMethod->update($validated);

        return back()->with('success', 'Sort order updated.');
    }

    /**
     * Get component name from type.
     */
    private function getComponentName(string $code): string
    {
        return match ($code) {
            'cod' => 'COD',
            'bank_transfer' => 'BankTransfer',
            'stripe' => 'Stripe',
            'razorpay' => 'Razorpay',
            default => 'Generic',
        };
    }
}
