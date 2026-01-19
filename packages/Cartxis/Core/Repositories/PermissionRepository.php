<?php

namespace Cartxis\Core\Repositories;

use Cartxis\Core\Models\Permission;
use Cartxis\Core\Models\Role;
use Illuminate\Support\Collection;

class PermissionRepository
{
    /**
     * Find permission by ID.
     */
    public function findPermission(int $id): ?Permission
    {
        return Permission::find($id);
    }

    /**
     * Find permission by name.
     */
    public function findPermissionByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Get permissions by group.
     */
    public function getPermissionsByGroup(string $group): Collection
    {
        return Permission::byGroup($group)->get();
    }

    /**
     * Create a new permission.
     */
    public function createPermission(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Update a permission.
     */
    public function updatePermission(int $id, array $data): bool
    {
        return Permission::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(int $id): bool
    {
        return Permission::where('id', $id)->delete() > 0;
    }

    /**
     * Find role by ID.
     */
    public function findRole(int $id): ?Role
    {
        return Role::find($id);
    }

    /**
     * Find role by name.
     */
    public function findRoleByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    /**
     * Get all roles.
     */
    public function getAllRoles(): Collection
    {
        return Role::all();
    }

    /**
     * Create a new role.
     */
    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update a role.
     */
    public function updateRole(int $id, array $data): bool
    {
        return Role::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete a role.
     */
    public function deleteRole(int $id): bool
    {
        return Role::where('id', $id)->delete() > 0;
    }

    /**
     * Assign permission to role.
     */
    public function assignPermissionToRole(Role $role, Permission $permission): void
    {
        $role->grantPermission($permission);
    }

    /**
     * Remove permission from role.
     */
    public function removePermissionFromRole(Role $role, Permission $permission): void
    {
        $role->revokePermission($permission);
    }

    /**
     * Sync role permissions.
     */
    public function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Get role permissions.
     */
    public function getRolePermissions(Role $role): Collection
    {
        return $role->permissions;
    }
}
