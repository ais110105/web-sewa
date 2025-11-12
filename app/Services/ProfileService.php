<?php

namespace App\Services;

use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Contracts\Services\ProfileServiceInterface;
use App\Models\Profile;

class ProfileService implements ProfileServiceInterface
{
    protected ProfileRepositoryInterface $profileRepository;

    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get user's profile
     */
    public function getUserProfile(int $userId): ?Profile
    {
        return $this->profileRepository->findByUserId($userId);
    }

    /**
     * Update or create user's profile
     */
    public function updateOrCreateProfile(int $userId, array $data): Profile
    {
        // Remove user_id from data to prevent override
        unset($data['user_id']);

        return $this->profileRepository->updateOrCreateForUser($userId, $data);
    }

    /**
     * Get authenticated user's profile
     */
    public function getMyProfile(): ?Profile
    {
        return $this->getUserProfile(auth()->id());
    }

    /**
     * Update authenticated user's profile
     */
    public function updateMyProfile(array $data): Profile
    {
        return $this->updateOrCreateProfile(auth()->id(), $data);
    }
}
