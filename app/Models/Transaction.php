<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'transaction_id',
        'gross_amount',
        'payment_type',
        'payment_method',
        'bank',
        'va_number',
        'status',
        'snap_token',
        'snap_url',
        'items',
        'midtrans_response',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'midtrans_response' => 'array',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settlement';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expire';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancel';
    }
}
