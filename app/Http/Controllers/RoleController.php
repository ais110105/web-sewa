<?php

namespace App\Http\Controllers;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(): View
    {
        $this->authorize('view-roles');

        $roles = $this->roleService->getPaginatedRoles(10);
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully!',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        $this->authorize('edit-roles');

        $role = $this->roleService->findRole($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray()
            ]
        ]);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->roleService->updateRole($id, $request->validated());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role updated successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update role'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete-roles');

        try {
            $result = $this->roleService->deleteRole($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
