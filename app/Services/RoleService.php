<?php

namespace App\Services;

use App\Contracts\Services\RoleServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleService implements RoleServiceInterface
{
    public function getPaginatedRoles(int $perPage = 10): LengthAwarePaginator
    {
        return Role::withCount('permissions', 'users')->latest()->paginate($perPage);
    }

    public function createRole(array $data): Role
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = Role::create($data);

        if (!empty($permissions)) {
            $this->syncPermissions($role, $permissions);
        }

        return $role;
    }

    public function updateRole(int $id, array $data): bool
    {
        $role = Role::findOrFail($id);

        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $result = $role->update($data);

        if ($result) {
            $this->syncPermissions($role, $permissions);
        }

        return $result;
    }

    public function deleteRole(int $id): bool
    {
        $role = Role::findOrFail($id);

        // Check if role is assigned to users
        if ($role->users()->count() > 0) {
            throw new \Exception('Cannot delete role that is assigned to users');
        }

        return $role->delete();
    }

    public function findRole(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
