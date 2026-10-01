<?php

namespace Cartxis\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\Admin\Services\AdminNotificationService;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuoteRequestController extends Controller
{
    public function __construct(protected AdminNotificationService $adminNotificationService) {}

    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('status', 'enabled')->firstOrFail();

        if (! $product->isQuote()) {
            return back()->with('error', 'Quotes are not available for this product.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1|max:99999',
            'message' => 'nullable|string|max:5000',
        ]);

        $quote = QuoteRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_price' => $product->special_price ?? $product->price,
            'ip_address' => $request->ip(),
            'status' => QuoteRequest::STATUS_NEW,
        ]);

        try {
            $this->adminNotificationService->notifyAllAdmins(
                type: 'quote.created',
                title: "New quote request {$quote->reference}",
                message: "{$quote->customer_name} requested a quote for {$product->name}.",
                actionUrl: "/admin/catalog/quote-requests/{$quote->id}",
                actorUserId: Auth::id(),
                entityType: 'quote_request',
                entityId: (int) $quote->id,
                meta: [
                    'reference' => $quote->reference,
                    'product_id' => $product->id,
                    'customer_email' => $quote->customer_email,
                ],
                severity: 'info'
            );
        } catch (\Throwable $e) {
            Log::error('Failed to notify admins of quote request', ['error' => $e->getMessage()]);
        }

        return back()->with('success', "Your quote request ({$quote->reference}) has been submitted. We will contact you shortly.");
    }
}
