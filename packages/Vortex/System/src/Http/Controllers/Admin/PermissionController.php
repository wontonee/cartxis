<?php

namespace Vortex\System\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Repositories\PermissionRepository;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionRepository $permissionRepository
    ) {
    }

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): Response
    {
        $permissions = $this->permissionRepository->getAllPermissions();

        // Group permissions by group
        $groupedPermissions = $permissions->groupBy('group')->map(function ($group) {
            return $group->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'group' => $permission->group,
                    'description' => $permission->description,
                    'created_at' => $permission->created_at->format('M d, Y'),
                ];
            });
        })->toArray();

        return Inertia::render('Admin/System/Permissions/Index', [
            'permissions' => $groupedPermissions,
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): Response
    {
        // Get existing groups for dropdown
        $groups = $this->permissionRepository->getAllPermissions()
            ->pluck('group')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('Admin/System/Permissions/Create', [
            'existingGroups' => $groups,
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->permissionRepository->createPermission($validator->validated());

        return redirect()
            ->route('admin.system.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the permission.
     */
    public function edit(int $id): Response
    {
        $permission = $this->permissionRepository->findPermission($id);

        if (!$permission) {
            abort(404, 'Permission not found');
        }

        // Get existing groups for dropdown
        $groups = $this->permissionRepository->getAllPermissions()
            ->pluck('group')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('Admin/System/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'group' => $permission->group,
                'description' => $permission->description,
            ],
            'existingGroups' => $groups,
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $permission = $this->permissionRepository->findPermission($id);

        if (!$permission) {
            abort(404, 'Permission not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'display_name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->permissionRepository->updatePermission($id, $validator->validated());

        return redirect()
            ->route('admin.system.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(int $id): RedirectResponse
    {
        $permission = $this->permissionRepository->findPermission($id);

        if (!$permission) {
            abort(404, 'Permission not found');
        }

        $this->permissionRepository->deletePermission($id);

        return redirect()
            ->route('admin.system.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
