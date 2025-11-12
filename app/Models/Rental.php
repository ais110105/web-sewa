<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'rental_code',
        'user_id',
        'transaction_id',
        'start_date',
        'end_date',
        'duration_days',
        'subtotal',
        'tax',
        'total_price',
        'status',
        'payment_status',
        'notes',
        'confirmed_at',
        'picked_up_at',
        'returned_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_price' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'rental_items')
            ->withPivot('quantity', 'price_per_day', 'subtotal', 'item_condition_pickup', 'item_condition_return', 'notes')
            ->withTimestamps();
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isOnRent(): bool
    {
        return $this->status === 'on_rent';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    // Generate rental code
    public static function generateRentalCode(): string
    {
        return 'RENT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Mark rental as returned and increase stock
     */
    public function markAsReturned(): bool
    {
        if (!$this->isOnRent() && !$this->isConfirmed()) {
            return false;
        }

        // Increase stock for each rental item
        foreach ($this->rentalItems as $rentalItem) {
            $rentalItem->item->increaseStock($rentalItem->quantity);
        }

        $this->update([
            'status' => 'completed',
            'returned_at' => now(),
        ]);

        return true;
    }
}
