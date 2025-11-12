<?php

namespace App\Services;

use App\Contracts\Services\CheckoutServiceInterface;
use App\Models\Cart;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutService implements CheckoutServiceInterface
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function processCheckout(User $user, array $data): Rental
    {
        return DB::transaction(function () use ($user, $data) {
            // Get cart items
            $cartItems = Cart::where('user_id', $user->id)
                ->with('item')
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Validate item availability
            $this->validateItemsAvailability($cartItems);

            // Create rental from cart
            $rental = $this->createRentalFromCart($user, $cartItems, $data);

            // Create transaction for payment
            $transaction = $this->createTransaction($user, $rental);

            // Update rental with transaction
            $rental->update(['transaction_id' => $transaction->id]);

            // Clear cart after successful checkout
            Cart::where('user_id', $user->id)->delete();

            return $rental->load(['transaction', 'rentalItems.item']);
        });
    }

    public function calculateTotal(Collection $cartItems): array
    {
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            // Price is per period, not per day. Only multiply by quantity
            $itemSubtotal = $cartItem->item->price_per_period * $cartItem->quantity;
            $subtotal += $itemSubtotal;
        }

        // No tax applied
        $total = $subtotal;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => 0,
            'total' => round($total, 2),
        ];
    }

    public function createRentalFromCart(User $user, Collection $cartItems, array $additionalData): Rental
    {
        // Calculate totals
        $totals = $this->calculateTotal($cartItems);

        // Get rental period from cart items (assuming all items have same dates)
        $firstItem = $cartItems->first();

        // Create rental
        $rental = Rental::create([
            'rental_code' => Rental::generateRentalCode(),
            'user_id' => $user->id,
            'start_date' => $firstItem->start_date,
            'end_date' => $firstItem->end_date,
            'duration_days' => $firstItem->duration_days,
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'total_price' => $totals['total'],
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $additionalData['notes'] ?? null,
        ]);

        // Create rental items
        foreach ($cartItems as $cartItem) {
            // Price is per period, only multiply by quantity
            $itemSubtotal = $cartItem->item->price_per_period * $cartItem->quantity;

            RentalItem::create([
                'rental_id' => $rental->id,
                'item_id' => $cartItem->item_id,
                'quantity' => $cartItem->quantity,
                'price_per_day' => $cartItem->item->price_per_period,
                'subtotal' => $itemSubtotal,
            ]);
        }

        return $rental;
    }

    public function cancelRental(Rental $rental): bool
    {
        if ($rental->status !== 'pending') {
            throw new \Exception('Only pending rentals can be cancelled');
        }

        // Cancel midtrans transaction if exists
        if ($rental->transaction && $rental->transaction->order_id) {
            try {
                $this->midtransService->cancelTransaction($rental->transaction->order_id);
            } catch (\Exception $e) {
                // Log error but continue with rental cancellation
                logger()->error('Failed to cancel Midtrans transaction: ' . $e->getMessage());
            }
        }

        // Update rental status
        $rental->update([
            'status' => 'cancelled',
        ]);

        return true;
    }

    protected function validateItemsAvailability(Collection $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            if ($cartItem->item->status !== 'available') {
                throw new \Exception("Item {$cartItem->item->name} is not available");
            }

            // Check stock availability
            if (!$cartItem->item->hasStock($cartItem->quantity)) {
                throw new \Exception("Item {$cartItem->item->name} does not have sufficient stock. Available: {$cartItem->item->available_stock}, Requested: {$cartItem->quantity}");
            }
        }
    }

    protected function createTransaction(User $user, Rental $rental): Transaction
    {
        // Prepare items for Midtrans
        $items = $rental->rentalItems->map(function ($rentalItem) {
            return [
                'id' => $rentalItem->item_id,
                'name' => $rentalItem->item->name,
                'price' => (int) $rentalItem->price_per_day,
                'quantity' => $rentalItem->quantity,
            ];
        })->toArray();

        // Create transaction via Midtrans
        $transaction = $this->midtransService->createTransaction([
            'user_id' => $user->id,
            'gross_amount' => $rental->total_price,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->profile->phone ?? '',
            'items' => $items,
            'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'shopeepay'],
        ]);

        return $transaction;
    }
}
