<?php

namespace App\Contracts\Services;

use App\Models\Profile;

interface ProfileServiceInterface
{
    /**
     * Get user's profile
     */
    public function getUserProfile(int $userId): ?Profile;

    /**
     * Update or create user's profile
     */
    public function updateOrCreateProfile(int $userId, array $data): Profile;

    /**
     * Get authenticated user's profile
     */
    public function getMyProfile(): ?Profile;

    /**
     * Update authenticated user's profile
     */
    public function updateMyProfile(array $data): Profile;
}
