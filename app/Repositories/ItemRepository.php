<?php

namespace App\Repositories;

use App\Contracts\Repositories\ItemRepositoryInterface;
use App\Models\Item;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }
}
