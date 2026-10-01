<?php

namespace Cartxis\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Product\Models\QuoteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuoteRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $query = QuoteRequest::query()->with('product:id,name,sku')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('Admin/QuoteRequests/Index', [
            'quotes' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(QuoteRequest $quoteRequest): Response
    {
        $quoteRequest->load('product');

        return Inertia::render('Admin/QuoteRequests/Show', [
            'quote' => $quoteRequest,
        ]);
    }

    public function update(Request $request, QuoteRequest $quoteRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,reviewed,quoted,closed',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $quoteRequest->update($validated);

        return back()->with('success', 'Quote request updated.');
    }

    public function destroy(QuoteRequest $quoteRequest)
    {
        $quoteRequest->delete();

        return redirect()
            ->route('admin.catalog.quote-requests.index')
            ->with('success', 'Quote request deleted.');
    }
}
