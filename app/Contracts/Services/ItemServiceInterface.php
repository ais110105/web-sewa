<?php

namespace App\Contracts\Services;

use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemServiceInterface
{
    /**
     * Get paginated items
     */
    public function getPaginatedItems(int $perPage = 10): LengthAwarePaginator;

    /**
     * Create new item
     */
    public function createItem(array $data): Item;

    /**
     * Update item
     */
    public function updateItem(int $id, array $data): bool;

    /**
     * Delete item
     */
    public function deleteItem(int $id): bool;

    /**
     * Find item by ID
     */
    public function findItem(int $id): ?Item;
}
