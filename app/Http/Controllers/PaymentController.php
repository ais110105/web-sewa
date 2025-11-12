<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'gross_amount' => 'required|numeric|min:1',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $user = Auth::user();

            $transaction = $this->midtransService->createTransaction([
                'user_id' => $user->id,
                'gross_amount' => $request->gross_amount,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? '',
                'items' => $request->items,
                'enabled_payments' => ['qris', 'bank_transfer'],
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $transaction->snap_token,
                'snap_url' => $transaction->snap_url,
                'order_id' => $transaction->order_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment checkout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat transaksi',
            ], 500);
        }
    }

    public function paymentPage(Request $request)
    {
        return view('payment.checkout');
    }

    public function history()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['rental.rentalItems.item'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('payment.history', compact('transactions'));
    }

    public function show($orderId)
    {
        $transaction = Transaction::where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('payment.detail', compact('transaction'));
    }

    public function checkStatus($orderId)
    {
        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $status = $this->midtransService->checkTransactionStatus($orderId);

            return response()->json([
                'success' => true,
                'status' => $status,
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Check status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status transaksi',
            ], 500);
        }
    }

    public function cancel($orderId)
    {
        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if (!$transaction->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dapat dibatalkan',
                ], 400);
            }

            $this->midtransService->cancelTransaction($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan',
            ]);
        } catch (\Exception $e) {
            Log::error('Cancel transaction error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi',
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $transaction = $this->midtransService->handleNotification($request->all());

            // Update rental status if payment is successful
            if ($transaction->status === 'settlement') {
                $rental = Rental::where('transaction_id', $transaction->id)->first();

                if ($rental) {
                    $rental->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                    ]);

                    // Decrease stock for each rental item
                    foreach ($rental->rentalItems as $rentalItem) {
                        $rentalItem->item->decreaseStock($rentalItem->quantity);
                    }

                    Log::info("Rental {$rental->rental_code} confirmed after payment and stock decreased");
                }
            }

            // Handle cancelled/expired payment
            if (in_array($transaction->status, ['expire', 'cancel', 'deny'])) {
                $rental = Rental::where('transaction_id', $transaction->id)->first();

                if ($rental && $rental->status === 'pending') {
                    $rental->update([
                        'status' => 'cancelled',
                    ]);

                    Log::info("Rental {$rental->rental_code} cancelled due to payment {$transaction->status}");
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
