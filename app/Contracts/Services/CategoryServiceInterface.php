<?php

namespace App\Contracts\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{
    /**
     * Get paginated categories
     */
    public function getPaginatedCategories(int $perPage = 10): LengthAwarePaginator;

    /**
     * Create new category
     */
    public function createCategory(array $data): Category;

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): bool;

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool;

    /**
     * Find category by ID
     */
    public function findCategory(int $id): ?Category;
}
