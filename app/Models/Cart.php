<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'start_date',
        'end_date',
        'duration_days',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getSubtotal(): float
    {
        // Price is per period, not per day. Only multiply by quantity
        return $this->item->price_per_period * $this->quantity;
    }
}
