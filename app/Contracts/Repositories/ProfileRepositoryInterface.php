<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface ProfileRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find profile by user ID
     */
    public function findByUserId(int $userId): ?Model;

    /**
     * Update or create profile for user
     */
    public function updateOrCreateForUser(int $userId, array $data): Model;
}
