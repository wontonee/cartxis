<?php

declare(strict_types=1);

namespace Vortex\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Customer\Http\Requests\StoreCustomerRequest;
use Vortex\Customer\Http\Requests\UpdateCustomerRequest;
use Vortex\Customer\Http\Resources\CustomerCollection;
use Vortex\Customer\Http\Resources\CustomerResource;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerGroup;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        $query = Customer::with('customerGroup')
            ->select('customers.*');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Filter by customer group
        if ($request->filled('customer_group_id')) {
            $query->where('customer_group_id', $request->input('customer_group_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by customer type (registered/guest)
        if ($request->filled('customer_type')) {
            $customerType = $request->input('customer_type');
            if ($customerType === 'registered') {
                $query->where('is_guest', false);
            } elseif ($customerType === 'guest') {
                $query->where('is_guest', true);
            }
        }

        // Filter by verified status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Filter by newsletter subscription
        if ($request->filled('newsletter_subscribed')) {
            $query->where('newsletter_subscribed', $request->boolean('newsletter_subscribed'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $customers = $query->paginate(15)->withQueryString();

        // Statistics
        $statistics = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('is_active', true)->count(),
            'verified_customers' => Customer::where('is_verified', true)->count(),
            'newsletter_subscribers' => Customer::where('newsletter_subscribed', true)->count(),
            'guest_customers' => Customer::where('is_guest', true)->count(),
            'registered_customers' => Customer::where('is_guest', false)->count(),
        ];

        return Inertia::render('Admin/Customer/Customers/Index', [
            'customers' => new CustomerCollection($customers),
            'customerGroups' => CustomerGroup::active()->orderBy('order')->get(),
            'filters' => $request->only(['search', 'customer_group_id', 'status', 'customer_type', 'is_verified', 'newsletter_subscribed', 'date_from', 'date_to']),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Customer/Customers/Create', [
            'customerGroups' => CustomerGroup::active()->orderBy('order')->get(),
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): Response
    {
        $customer->load([
            'customerGroup',
            'addresses' => function ($query) {
                $query->orderBy('is_default_shipping', 'desc')
                    ->orderBy('is_default_billing', 'desc');
            },
            'notes' => function ($query) {
                $query->with('user:id,name')->latest();
            },
        ]);

        return Inertia::render('Admin/Customer/Customers/Show', [
            'customer' => (new CustomerResource($customer))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        $customer->load('customerGroup');

        return Inertia::render('Admin/Customer/Customers/Edit', [
            'customer' => (new CustomerResource($customer))->resolve(),
            'customerGroups' => CustomerGroup::active()->orderBy('order')->get(),
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Export customers to CSV.
     */
    public function export(Request $request)
    {
        $query = Customer::with('customerGroup');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_group_id')) {
            $query->where('customer_group_id', $request->input('customer_group_id'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $customers = $query->get();

        $filename = 'customers_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Customer Group',
                'Company',
                'Total Orders',
                'Total Spent',
                'Status',
                'Verified',
                'Newsletter',
                'Created At',
            ]);

            // Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->first_name,
                    $customer->last_name,
                    $customer->email,
                    $customer->phone,
                    $customer->customerGroup->name ?? '',
                    $customer->company_name,
                    $customer->total_orders,
                    $customer->total_spent,
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->is_verified ? 'Yes' : 'No',
                    $customer->newsletter_subscribed ? 'Yes' : 'No',
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk update customers status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
            'is_active' => 'required|boolean',
        ]);

        Customer::whereIn('id', $request->input('customer_ids'))
            ->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Customers updated successfully.');
    }

    /**
     * Bulk delete customers.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        Customer::whereIn('id', $request->input('customer_ids'))->delete();

        return back()->with('success', 'Customers deleted successfully.');
    }
}
