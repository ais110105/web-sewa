<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService implements CategoryServiceInterface
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getPaginatedCategories(int $perPage = 10): LengthAwarePaginator
    {
        return Category::latest()->paginate($perPage);
    }

    public function createCategory(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    public function findCategory(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }
}
