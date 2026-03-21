<?php

namespace Cartxis\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Cartxis\Customer\Models\Customer;

class NewsletterController extends Controller
{
    /**
     * Display the newsletter subscriber list.
     */
    public function index(Request $request): Response
    {
        $query = Customer::query()
            ->where('newsletter_subscribed', true)
            ->select(['id', 'first_name', 'last_name', 'email', 'is_active', 'is_guest', 'updated_at', 'created_at']);

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filter by account type
        if ($type = $request->get('type')) {
            if ($type === 'registered') {
                $query->where('is_guest', false);
            } elseif ($type === 'guest') {
                $query->where('is_guest', true);
            }
        }

        // Sort
        $sortBy    = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['first_name', 'last_name', 'email', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $subscribers = $query->paginate(25)->withQueryString();

        return Inertia::render('Admin/Marketing/Newsletters/Index', [
            'subscribers' => $subscribers,
            'filters' => $request->only(['search', 'type', 'sort_by', 'sort_order']),
            'stats' => [
                'total'      => Customer::where('newsletter_subscribed', true)->count(),
                'registered' => Customer::where('newsletter_subscribed', true)->where('is_guest', false)->count(),
                'guest'      => Customer::where('newsletter_subscribed', true)->where('is_guest', true)->count(),
            ],
        ]);
    }

    /**
     * Unsubscribe a specific subscriber (admin action).
     */
    public function unsubscribe(Request $request, Customer $customer): JsonResponse
    {
        $customer->update(['newsletter_subscribed' => false]);

        return response()->json(['success' => true, 'message' => 'Subscriber removed successfully.']);
    }

    /**
     * Bulk unsubscribe selected subscribers.
     */
    public function bulkUnsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:customers,id',
        ]);

        Customer::whereIn('id', $validated['ids'])
            ->update(['newsletter_subscribed' => false]);

        return response()->json([
            'success' => true,
            'message' => count($validated['ids']) . ' subscriber(s) removed.',
        ]);
    }

    /**
     * Export subscriber list as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Customer::query()
            ->where('newsletter_subscribed', true)
            ->select(['id', 'first_name', 'last_name', 'email', 'is_guest', 'updated_at'])
            ->orderBy('updated_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            if ($type === 'registered') {
                $query->where('is_guest', false);
            } elseif ($type === 'guest') {
                $query->where('is_guest', true);
            }
        }

        $filename  = 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Email', 'Type', 'Subscribed At']);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->first_name,
                        $row->last_name,
                        $row->email,
                        $row->is_guest ? 'Guest' : 'Registered',
                        $row->updated_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
