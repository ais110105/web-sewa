<?php

namespace App\Services;

use App\Contracts\Repositories\ItemRepositoryInterface;
use App\Contracts\Services\ItemServiceInterface;
use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ItemService implements ItemServiceInterface
{
    protected ItemRepositoryInterface $itemRepository;

    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getPaginatedItems(int $perPage = 10): LengthAwarePaginator
    {
        return Item::with("category")->latest()->paginate($perPage);
    }

    public function createItem(array $data): Item
    {
        // Handle photo upload
        if (isset($data["photo"]) && $data["photo"]) {
            $data["photo_url"] = $data["photo"]->store("items", "public");
            unset($data["photo"]);
        }

        return $this->itemRepository->create($data);
    }

    public function updateItem(int $id, array $data): bool
    {
        $item = $this->findItem($id);

        if (!$item) {
            return false;
        }

        // Handle photo upload
        if (isset($data["photo"]) && $data["photo"]) {
            // Delete old photo if exists
            if ($item->photo_url) {
                Storage::disk("public")->delete($item->photo_url);
            }

            $data["photo_url"] = $data["photo"]->store("items", "public");
            unset($data["photo"]);
        }

        return $this->itemRepository->update($id, $data);
    }

    public function deleteItem(int $id): bool
    {
        $item = $this->findItem($id);

        if (!$item) {
            return false;
        }

        // Delete photo if exists
        if ($item->photo_url) {
            Storage::disk("public")->delete($item->photo_url);
        }

        return $this->itemRepository->delete($id);
    }

    public function findItem(int $id): ?Item
    {
        return $this->itemRepository->find($id);
    }
}
