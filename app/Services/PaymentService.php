<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(protected MidtransService $midtransService) {}

    /**
     * Settle rental setelah payment sukses.
     * Idempotent: tidak melakukan apa-apa jika rental sudah di-settle sebelumnya.
     */
    public function settleRental(Rental $rental): void
    {
        $rental->loadMissing('rentalItems', 'transaction');

        // Fast-path idempotency check sebelum acquire lock.
        if (in_array($rental->payment_status, ['paid', 'refunded', 'pending_refund'], true)) {
            return;
        }

        $itemIds = $rental->rentalItems->pluck('item_id')->all();

        $stockOk = DB::transaction(function () use ($rental, $itemIds) {
            // Re-check di dalam transaksi setelah lock untuk mencegah double-settle
            // pada concurrent webhook calls.
            $rental->refresh();
            if (in_array($rental->payment_status, ['paid', 'refunded', 'pending_refund'], true)) {
                return true; // sudah di-settle oleh request lain, anggap sukses
            }

            $items = Item::whereIn('id', $itemIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($rental->rentalItems as $ri) {
                $item = $items[$ri->item_id] ?? null;
                if (!$item || $item->available_stock < $ri->quantity) {
                    return false;
                }
            }

            foreach ($rental->rentalItems as $ri) {
                $items[$ri->item_id]->decrement('available_stock', $ri->quantity);
            }

            $rental->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            if ($rental->transaction) {
                $rental->transaction->update(['paid_at' => now()]);
            }

            return true;
        });

        if ($stockOk) {
            return;
        }

        $this->cancelAndRefund($rental);
    }

    protected function cancelAndRefund(Rental $rental): void
    {
        $orderId = $rental->transaction?->order_id;
        $reasonNote = 'auto-cancelled: stok habis (race condition)';

        try {
            if ($orderId) {
                $this->midtransService->refundTransaction(
                    $orderId,
                    (int) $rental->total_price,
                    $reasonNote,
                );
            }

            $rental->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'notes' => trim(($rental->notes ?? '') . "\n" . $reasonNote),
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-refund failed; marked pending_refund', [
                'rental_id' => $rental->id,
                'error' => $e->getMessage(),
            ]);
            $rental->update([
                'status' => 'cancelled',
                'payment_status' => 'pending_refund',
                'notes' => trim(($rental->notes ?? '') . "\n" . $reasonNote . " (refund manual: {$e->getMessage()})"),
            ]);
        }
    }
}
