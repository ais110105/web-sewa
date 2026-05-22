<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionManagementController extends Controller
{
    /**
     * Display all transactions (rentals + payments) for admin management
     */
    public function index(Request $request)
    {
        $query = Rental::with([
            "user",
            "rentalItems.item.category",
            "transaction",
        ])->latest();

        // Filter by status
        if ($request->has("status") && $request->status !== "all") {
            if ($request->status === "overdue") {
                $query->overdue();
            } else {
                $query->where("status", $request->status);
            }
        }

        // Search by rental code, order ID, or user name
        if ($request->has("search") && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("rental_code", "like", "%{$search}%")
                    ->orWhereHas("user", function ($q) use ($search) {
                        $q->where("name", "like", "%{$search}%")->orWhere(
                            "email",
                            "like",
                            "%{$search}%",
                        );
                    })
                    ->orWhereHas("transaction", function ($q) use ($search) {
                        $q->where("order_id", "like", "%{$search}%");
                    });
            });
        }

        $rentals = $query->paginate(15);

        return view("management.transactions.index", compact("rentals"));
    }

    /**
     * Update rental status
     */
    public function updateStatus(Request $request, Rental $rental)
    {
        $request->validate([
            "status" => "required|in:confirmed,on_rent,completed,cancelled",
        ]);

        $oldStatus = $rental->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            // Update status
            $rental->update(["status" => $newStatus]);

            // Set timestamp based on status
            if ($newStatus === "confirmed" && !$rental->confirmed_at) {
                $rental->update(["confirmed_at" => now()]);
            } elseif ($newStatus === "on_rent" && !$rental->picked_up_at) {
                $rental->update(["picked_up_at" => now()]);
            } elseif ($newStatus === "completed" && !$rental->returned_at) {
                $rental->update(["returned_at" => now()]);

                // Restore available stock when completed
                foreach ($rental->rentalItems as $rentalItem) {
                    $rentalItem->item->increment(
                        "available_stock",
                        $rentalItem->quantity,
                    );
                }
            }

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Status berhasil diupdate",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal mengupdate status: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * GET data untuk modal form pengembalian.
     */
    public function returnFormData(\App\Models\Rental $rental)
    {
        $rental->load('rentalItems.item');

        return response()->json([
            'success' => true,
            'data' => [
                'rental_code' => $rental->rental_code,
                'items' => $rental->rentalItems->map(function ($ri) {
                    return [
                        'rental_item_id' => $ri->id,
                        'item_name' => $ri->item->name,
                        'quantity' => $ri->quantity,
                    ];
                }),
            ],
        ]);
    }

    /**
     * POST: simpan kondisi tiap item + ubah ke completed.
     */
    public function completeReturn(\Illuminate\Http\Request $request, \App\Models\Rental $rental)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.rental_item_id' => 'required|integer|exists:rental_items,id',
            'items.*.condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            if (!$rental->completeReturn($validated['items'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental tidak dapat diselesaikan dari status saat ini',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil dicatat',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics data
     */
    public function statistics()
    {
        $stats = [
            "total" => Rental::count(),
            "confirmed" => Rental::where("status", "confirmed")->count(),
            "on_rent" => Rental::where("status", "on_rent")->count(),
            "completed" => Rental::where("status", "completed")->count(),
            "total_revenue" => Rental::where("payment_status", "paid")->sum(
                "total_price",
            ),
        ];

        return response()->json($stats);
    }
}
