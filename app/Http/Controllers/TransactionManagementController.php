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
            $query->where("status", $request->status);
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
