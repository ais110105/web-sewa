<?php

namespace App\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

interface RoleServiceInterface
{
    /**
     * Get paginated roles
     */
    public function getPaginatedRoles(int $perPage = 10): LengthAwarePaginator;

    /**
     * Create new role
     */
    public function createRole(array $data): Role;

    /**
     * Update role
     */
    public function updateRole(int $id, array $data): bool;

    /**
     * Delete role
     */
    public function deleteRole(int $id): bool;

    /**
     * Find role by ID
     */
    public function findRole(int $id): ?Role;

    /**
     * Sync permissions to role
     */
    public function syncPermissions(Role $role, array $permissions): void;
}
