<?php

namespace App\Services;

use App\Contracts\Repositories\ItemRepositoryInterface;
use App\Contracts\Services\ItemServiceInterface;
use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService implements ItemServiceInterface
{
    protected ItemRepositoryInterface $itemRepository;

    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getPaginatedItems(int $perPage = 10): LengthAwarePaginator
    {
        return Item::with('category')->latest()->paginate($perPage);
    }

    public function createItem(array $data): Item
    {
        return $this->itemRepository->create($data);
    }

    public function updateItem(int $id, array $data): bool
    {
        return $this->itemRepository->update($id, $data);
    }

    public function deleteItem(int $id): bool
    {
        return $this->itemRepository->delete($id);
    }

    public function findItem(int $id): ?Item
    {
        return $this->itemRepository->find($id);
    }
}
