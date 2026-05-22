<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    private function configureMidtrans(): void
    {
        // Set config dengan nilai eksplisit
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = false; // Force sandbox
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Log configuration untuk debugging
        \Log::info('Midtrans Config Set', [
            'server_key' => substr(Config::$serverKey, 0, 15) . '...',
            'client_key' => Config::$clientKey,
            'is_production' => Config::$isProduction,
        ]);
    }

    public function createTransaction(array $params): Transaction
    {
        $orderId = 'ORDER-' . time() . '-' . uniqid();

        $transaction = Transaction::create([
            'user_id' => $params['user_id'],
            'order_id' => $orderId,
            'gross_amount' => $params['gross_amount'],
            'items' => $params['items'] ?? [],
            'status' => 'pending',
        ]);

        $snapParams = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $params['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $params['customer_name'],
                'email' => $params['customer_email'],
                'phone' => $params['customer_phone'] ?? '',
            ],
            'item_details' => $this->formatItemDetails($params['items'] ?? []),
            'enabled_payments' => $params['enabled_payments'] ?? ['qris', 'bank_transfer'],
        ];

        try {
            \Log::info('Creating Midtrans transaction', [
                'order_id' => $orderId,
                'amount' => $params['gross_amount'],
                'server_key' => substr(Config::$serverKey, 0, 15) . '...',
                'client_key' => Config::$clientKey,
                'is_production' => Config::$isProduction,
                'snap_params' => json_encode($snapParams),
            ]);

            // Use createTransaction which returns token AND redirect_url
            $snapResponse = Snap::createTransaction($snapParams);

            \Log::info('Midtrans API Response', [
                'order_id' => $orderId,
                'response' => json_encode($snapResponse),
            ]);

            $snapToken = $snapResponse->token;
            $snapUrl = $snapResponse->redirect_url;

            $transaction->update([
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
                'midtrans_response' => json_decode(json_encode($snapResponse), true),
            ]);

            \Log::info('Midtrans transaction created successfully', [
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            \Log::error('Midtrans transaction failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            $transaction->update([
                'status' => 'failed',
                'midtrans_response' => [
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                ],
            ]);

            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function handleNotification(array $notificationData): Transaction
    {
        $notification = new Notification();

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentType = $notification->payment_type;

        $transaction = Transaction::where('order_id', $orderId)->firstOrFail();

        // Update transaction details from notification
        $transaction->transaction_id = $notification->transaction_id;
        $transaction->payment_type = $paymentType;

        // Handle different payment types
        if ($paymentType === 'qris') {
            $transaction->payment_method = 'qris';
        } elseif ($paymentType === 'bank_transfer') {
            $transaction->payment_method = 'bank_transfer';
            if (isset($notification->va_numbers[0])) {
                $transaction->bank = $notification->va_numbers[0]->bank;
                $transaction->va_number = $notification->va_numbers[0]->va_number;
            }
        }

        // Update status based on transaction status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $transaction->status = 'settlement';
                $transaction->paid_at = now();
            }
        } elseif ($transactionStatus == 'settlement') {
            $transaction->status = 'settlement';
            $transaction->paid_at = now();
        } elseif ($transactionStatus == 'pending') {
            $transaction->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaction->status = $transactionStatus;
        }

        $transaction->midtrans_response = json_decode(json_encode($notification), true);
        $transaction->save();

        return $transaction;
    }

    public function checkTransactionStatus(string $orderId): array
    {
        try {
            $status = MidtransTransaction::status($orderId);
            return json_decode(json_encode($status), true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function cancelTransaction(string $orderId): bool
    {
        try {
            MidtransTransaction::cancel($orderId);

            $transaction = Transaction::where('order_id', $orderId)->first();
            if ($transaction) {
                $transaction->update(['status' => 'cancel']);
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Refund transaksi via Midtrans API.
     * Untuk metode yang tidak support instant refund (mis. bank transfer manual),
     * API akan return error; caller wajib catch & tindak lanjut.
     */
    public function refundTransaction(string $orderId, ?int $amount = null, string $reason = 'Auto refund'): array
    {
        try {
            $params = ['reason' => $reason];
            if ($amount !== null) {
                $params['amount'] = $amount;
            }

            $response = MidtransTransaction::refund($orderId, $params);

            $transaction = Transaction::where('order_id', $orderId)->first();
            if ($transaction) {
                $transaction->update(['status' => 'refund']);
            }

            return is_object($response) ? json_decode(json_encode($response), true) : (array) $response;
        } catch (\Exception $e) {
            \Log::error('Midtrans refund failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function formatItemDetails(array $items): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'] ?? uniqid(),
                'price' => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name' => $item['name'],
            ];
        }, $items);
    }
}
