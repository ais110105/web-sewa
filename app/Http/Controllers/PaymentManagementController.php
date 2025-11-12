<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentManagementController extends Controller
{
    /**
     * Display all transactions for admin
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'rental.rentalItems.item'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by order_id or user name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->paginate(20);

        return view('payments.management.index', compact('transactions'));
    }

    /**
     * Display transaction detail
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'rental.rentalItems.item.category']);
        return view('payments.management.show', compact('transaction'));
    }

    /**
     * Get payment statistics
     */
    public function statistics()
    {
        $stats = [
            'total_transactions' => Transaction::count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'settlement' => Transaction::where('status', 'settlement')->count(),
            'expire' => Transaction::where('status', 'expire')->count(),
            'cancel' => Transaction::where('status', 'cancel')->count(),
            'total_revenue' => Transaction::where('status', 'settlement')->sum('gross_amount'),
            'today_revenue' => Transaction::where('status', 'settlement')
                ->whereDate('paid_at', today())
                ->sum('gross_amount'),
            'this_month_revenue' => Transaction::where('status', 'settlement')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('gross_amount'),
        ];

        return response()->json($stats);
    }
}
