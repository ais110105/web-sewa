<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create user with role
     */
    public function createWithRole(array $data, string $role): User;
}
