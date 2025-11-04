<?php

declare(strict_types=1);

namespace Vortex\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Customer\Http\Requests\StoreCustomerGroupRequest;
use Vortex\Customer\Http\Requests\UpdateCustomerGroupRequest;
use Vortex\Customer\Http\Resources\CustomerGroupResource;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerGroup;

class CustomerGroupController extends Controller
{
    /**
     * Display a listing of customer groups.
     */
    public function index(Request $request): Response
    {
        $query = CustomerGroup::withCount(['customers', 'activeCustomers']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('status', true);
            } elseif ($status === 'inactive') {
                $query->where('status', false);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'order');
        $sortOrder = $request->input('sort_order', 'asc');
        
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $groups = $query->paginate($perPage);

        // Calculate statistics
        $statistics = [
            'total' => CustomerGroup::count(),
            'active' => CustomerGroup::where('status', true)->count(),
            'inactive' => CustomerGroup::where('status', false)->count(),
            'with_customers' => CustomerGroup::has('customers')->count(),
        ];

        return Inertia::render('Admin/Customer/Groups/Index', [
            'groups' => CustomerGroupResource::collection($groups),
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new customer group.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Customer/Groups/Create');
    }

    /**
     * Store a newly created customer group.
     */
    public function store(StoreCustomerGroupRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // Auto-generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = Str::slug($data['name']);
            }
            
            // Set order to be last if not provided
            if (!isset($data['order'])) {
                $data['order'] = CustomerGroup::max('order') + 1;
            }
            
            // If this is set as default, unset all other defaults
            if (!empty($data['is_default'])) {
                CustomerGroup::where('is_default', true)->update(['is_default' => false]);
            }
            
            $group = CustomerGroup::create($data);
            
            DB::commit();
            
            return redirect()
                ->route('admin.customers.groups.index')
                ->with('success', "Customer group '{$group->name}' created successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create customer group: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a customer group.
     */
    public function edit(CustomerGroup $group): Response
    {
        $group->load(['customers' => function ($query) {
            $query->select('id', 'first_name', 'last_name', 'email', 'customer_group_id')
                ->limit(10);
        }]);
        
        return Inertia::render('Admin/Customer/Groups/Edit', [
            'group' => (new CustomerGroupResource($group))->resolve(),
            'customersCount' => $group->customers()->count(),
        ]);
    }

    /**
     * Update the specified customer group.
     */
    public function update(UpdateCustomerGroupRequest $request, CustomerGroup $group): RedirectResponse
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // If this is set as default, unset all other defaults
            if (!empty($data['is_default']) && !$group->is_default) {
                CustomerGroup::where('is_default', true)
                    ->where('id', '!=', $group->id)
                    ->update(['is_default' => false]);
            }
            
            $group->update($data);
            
            DB::commit();
            
            return redirect()
                ->route('admin.customers.groups.index')
                ->with('success', "Customer group '{$group->name}' updated successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update customer group: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer group.
     */
    public function destroy(CustomerGroup $group): RedirectResponse
    {
        // Prevent deletion if group has customers (including soft-deleted)
        $customersCount = $group->customers()->withTrashed()->count();
        if ($customersCount > 0) {
            return redirect()
                ->back()
                ->with('error', "Cannot delete '{$group->name}' because it has {$customersCount} customer(s) (including soft-deleted). Please reassign or permanently delete the customers first.");
        }
        
        // Prevent deletion of default group
        if ($group->is_default) {
            return redirect()
                ->back()
                ->with('error', "Cannot delete the default customer group. Please set another group as default first.");
        }
        
        try {
            $name = $group->name;
            $group->delete();
            
            return redirect()
                ->route('admin.customers.groups.index')
                ->with('success', "Customer group '{$name}' deleted successfully.");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete customer group: ' . $e->getMessage());
        }
    }

    /**
     * Update the order of customer groups (drag-and-drop reordering).
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:customer_groups,id',
            'groups.*.order' => 'required|integer|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            foreach ($request->input('groups') as $groupData) {
                CustomerGroup::where('id', $groupData['id'])
                    ->update(['order' => $groupData['order']]);
            }
            
            DB::commit();
            
            return redirect()
                ->back()
                ->with('success', 'Customer group order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to update group order: ' . $e->getMessage());
        }
    }

    /**
     * Apply auto-assignment rules to customers.
     */
    public function applyAutoAssignment(CustomerGroup $group): RedirectResponse
    {
        if (!$group->auto_assignment_rules || empty($group->auto_assignment_rules)) {
            return redirect()
                ->back()
                ->with('error', 'No auto-assignment rules configured for this group.');
        }
        
        DB::beginTransaction();
        
        try {
            $rules = $group->auto_assignment_rules;
            $query = Customer::where('is_guest', false); // Only registered customers
            
            // Apply rules
            if (isset($rules['min_orders']) && $rules['min_orders'] > 0) {
                $query->where('total_orders', '>=', $rules['min_orders']);
            }
            
            if (isset($rules['min_spent']) && $rules['min_spent'] > 0) {
                $query->where('total_spent', '>=', $rules['min_spent']);
            }
            
            if (isset($rules['min_aov']) && $rules['min_aov'] > 0) {
                $query->whereRaw('(total_spent / NULLIF(total_orders, 0)) >= ?', [$rules['min_aov']]);
            }
            
            // Update matching customers
            $affected = $query->update(['customer_group_id' => $group->id]);
            
            DB::commit();
            
            return redirect()
                ->back()
                ->with('success', "Auto-assignment applied successfully. {$affected} customer(s) assigned to '{$group->name}'.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to apply auto-assignment: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete customer groups.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:customer_groups,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $groups = CustomerGroup::whereIn('id', $request->input('ids'))->get();
            
            $deleted = 0;
            $errors = [];
            
            foreach ($groups as $group) {
                // Check if group has customers including soft-deleted
                if ($group->customers()->withTrashed()->count() > 0) {
                    $errors[] = "'{$group->name}' has customers";
                    continue;
                }
                
                // Check if default
                if ($group->is_default) {
                    $errors[] = "'{$group->name}' is default group";
                    continue;
                }
                
                $group->delete();
                $deleted++;
            }
            
            DB::commit();
            
            $message = "{$deleted} customer group(s) deleted successfully.";
            if (!empty($errors)) {
                $message .= ' Skipped: ' . implode(', ', $errors);
            }
            
            return redirect()
                ->back()
                ->with($deleted > 0 ? 'success' : 'warning', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete customer groups: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update status of customer groups.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:customer_groups,id',
            'status' => 'required|boolean',
        ]);
        
        try {
            $affected = CustomerGroup::whereIn('id', $request->input('ids'))
                ->update(['status' => $request->boolean('status')]);
            
            $statusText = $request->boolean('status') ? 'activated' : 'deactivated';
            
            return redirect()
                ->back()
                ->with('success', "{$affected} customer group(s) {$statusText} successfully.");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update customer groups: ' . $e->getMessage());
        }
    }
}
