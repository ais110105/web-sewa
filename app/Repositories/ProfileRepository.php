<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    public function __construct(Profile $model)
    {
        parent::__construct($model);
    }

    /**
     * Find profile by user ID
     */
    public function findByUserId(int $userId): ?Model
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Update or create profile for user
     */
    public function updateOrCreateForUser(int $userId, array $data): Model
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }
}
