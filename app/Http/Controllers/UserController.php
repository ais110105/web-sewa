<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('view-users');

        $users = $this->userService->getPaginatedUsers(10);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $this->authorize('edit-users');

        $user = $this->userService->findUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'user'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->userService->updateUser($id, $request->validated());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete-users');

        try {
            // Prevent self-deletion
            if ($id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $result = $this->userService->deleteUser($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}
