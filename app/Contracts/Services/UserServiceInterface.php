<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Get paginated users
     */
    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator;

    /**
     * Create new user
     */
    public function createUser(array $data): User;

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): bool;

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool;

    /**
     * Find user by ID
     */
    public function findUser(int $id): ?User;

    /**
     * Assign role to user
     */
    public function assignRole(User $user, string $role): void;
}
