<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\CheckoutServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Rental;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected CheckoutServiceInterface $checkoutService;
    protected MidtransService $midtransService;

    public function __construct(CheckoutServiceInterface $checkoutService, MidtransService $midtransService)
    {
        $this->checkoutService = $checkoutService;
        $this->midtransService = $midtransService;
    }

    /**
     * Display rental history page
     */
    public function historyView(Request $request): View
    {
        $user = $request->user();
        $rentals = Rental::where('user_id', $user->id)
            ->with(['rentalItems.item.category', 'transaction'])
            ->latest()
            ->paginate(10);

        return view('rentals.history', compact('rentals'));
    }

    /**
     * Process checkout from cart
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $rental = $this->checkoutService->processCheckout($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Checkout successful',
                'data' => [
                    'rental' => $rental,
                    'payment' => [
                        'snap_token' => $rental->transaction->snap_token,
                        'snap_url' => $rental->transaction->snap_url,
                        'order_id' => $rental->transaction->order_id,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's rental history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $rentals = Rental::where('user_id', $user->id)
            ->with(['rentalItems.item', 'transaction'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $rentals,
        ]);
    }

    /**
     * Get rental detail
     */
    public function show(Request $request, Rental $rental): JsonResponse
    {
        // Check if rental belongs to user
        if ($rental->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $rental->load(['rentalItems.item', 'transaction']);

        return response()->json([
            'success' => true,
            'data' => $rental,
        ]);
    }

    /**
     * Cancel rental
     */
    public function cancel(Request $request, Rental $rental): JsonResponse
    {
        try {
            // Check if rental belongs to user
            if ($rental->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $this->checkoutService->cancelRental($rental);

            return response()->json([
                'success' => true,
                'message' => 'Rental cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel rental: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Regenerate payment token for expired transaction
     */
    public function regeneratePayment(Request $request, Rental $rental): JsonResponse
    {
        try {
            // Check if rental belongs to user
            if ($rental->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Only regenerate for pending unpaid rentals
            if ($rental->status !== 'pending' || $rental->payment_status !== 'unpaid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot regenerate payment for this rental',
                ], 400);
            }

            $rental->load(['transaction', 'rentalItems.item']);

            // Prepare items for Midtrans
            $items = $rental->rentalItems->map(function ($rentalItem) {
                return [
                    'id' => $rentalItem->item_id,
                    'name' => $rentalItem->item->name,
                    'price' => (int) $rentalItem->price_per_day,
                    'quantity' => $rentalItem->quantity,
                ];
            })->toArray();

            // Create new transaction
            $newTransaction = $this->midtransService->createTransaction([
                'user_id' => $rental->user_id,
                'gross_amount' => $rental->total_price,
                'customer_name' => $request->user()->name,
                'customer_email' => $request->user()->email,
                'customer_phone' => $request->user()->profile->phone ?? '',
                'items' => $items,
                'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'shopeepay'],
            ]);

            // Update rental with new transaction
            $rental->update(['transaction_id' => $newTransaction->id]);

            // Delete old transaction
            if ($rental->transaction) {
                $rental->transaction->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment link regenerated successfully',
                'data' => [
                    'snap_token' => $newTransaction->snap_token,
                    'snap_url' => $newTransaction->snap_url,
                    'order_id' => $newTransaction->order_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate payment: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check and update payment status manually (for local development)
     */
    public function checkPaymentStatus(Request $request, Rental $rental): JsonResponse
    {
        try {
            // Check if rental belongs to user
            if ($rental->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $rental->load('transaction');

            // Check status from Midtrans API
            $midtransStatus = $this->midtransService->checkTransactionStatus($rental->transaction->order_id);

            // Map Midtrans status
            $statusMap = [
                'capture' => 'settlement',
                'settlement' => 'settlement',
                'pending' => 'pending',
                'deny' => 'deny',
                'expire' => 'expire',
                'cancel' => 'cancel',
            ];

            $newStatus = $statusMap[$midtransStatus['transaction_status']] ?? 'pending';

            // Update transaction
            $rental->transaction->update([
                'status' => $newStatus,
                'payment_type' => $midtransStatus['payment_type'] ?? null,
                'transaction_id' => $midtransStatus['transaction_id'] ?? null,
            ]);

            // Update rental based on payment status
            if ($newStatus === 'settlement') {
                $rental->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                $rental->transaction->update(['paid_at' => now()]);

                // Decrease stock for each rental item
                foreach ($rental->rentalItems as $rentalItem) {
                    $rentalItem->item->decreaseStock($rentalItem->quantity);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed! Rental status updated.',
                    'data' => [
                        'payment_status' => 'paid',
                        'rental_status' => 'confirmed',
                    ],
                ]);
            } elseif (in_array($newStatus, ['expire', 'cancel', 'deny'])) {
                $rental->update(['status' => 'cancelled']);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment ' . $newStatus,
                    'data' => [
                        'payment_status' => $rental->payment_status,
                        'rental_status' => 'cancelled',
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment still pending',
                'data' => [
                    'payment_status' => $rental->payment_status,
                    'rental_status' => $rental->status,
                    'transaction_status' => $newStatus,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get payment status
     */
    public function paymentStatus(Request $request, Rental $rental): JsonResponse
    {
        // Check if rental belongs to user
        if ($rental->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $rental->load('transaction');

        return response()->json([
            'success' => true,
            'data' => [
                'rental_status' => $rental->status,
                'payment_status' => $rental->payment_status,
                'transaction' => [
                    'order_id' => $rental->transaction->order_id,
                    'status' => $rental->transaction->status,
                    'payment_type' => $rental->transaction->payment_type,
                    'payment_method' => $rental->transaction->payment_method,
                    'va_number' => $rental->transaction->va_number,
                    'bank' => $rental->transaction->bank,
                    'paid_at' => $rental->transaction->paid_at,
                ],
            ],
        ]);
    }
}
