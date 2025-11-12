<?php

namespace App\Contracts\Services;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Attempt to authenticate user
     */
    public function attempt(array $credentials, bool $remember = false): bool;

    /**
     * Register a new user
     */
    public function register(array $data): User;

    /**
     * Logout the user
     */
    public function logout(): void;

    /**
     * Get authenticated user
     */
    public function user(): ?User;
}
