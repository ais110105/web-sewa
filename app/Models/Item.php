<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'photo_url',
        'status',
        'price_per_period',
        'stock',
        'available_stock',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_period' => 'decimal:2',
            'stock' => 'integer',
            'available_stock' => 'integer',
        ];
    }

    /**
     * Get the category that owns the item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Decrease available stock when item is rented
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->available_stock >= $quantity) {
            $this->available_stock -= $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Increase available stock when item is returned
     */
    public function increaseStock(int $quantity): bool
    {
        if ($this->available_stock + $quantity <= $this->stock) {
            $this->available_stock += $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Check if item has sufficient stock
     */
    public function hasStock(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }
}
