<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\Request;

class PrivilegedDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            "total_users" => User::count(),
            "total_items" => Item::count(),
            "active_rentals" => Rental::whereIn("status", [
                "confirmed",
                "on_rent",
            ])->count(),
            "pending_rentals" => Rental::where("status", "pending")->count(),
            "completed_rentals" => Rental::where(
                "status",
                "completed",
            )->count(),
            "total_revenue" => Rental::where("payment_status", "paid")->sum(
                "total_price",
            ),
            "pending_payments" => Rental::where(
                "payment_status",
                "unpaid",
            )->count(),
            "low_stock_items" => Item::where(
                "available_stock",
                "<=",
                2,
            )->count(),
        ];

        $recentRentals = Rental::with(["user", "rentalItems.item"])
            ->latest()
            ->take(10)
            ->get();

        $lowStockItems = Item::with("category")
            ->where("available_stock", "<=", 2)
            ->orderBy("available_stock")
            ->take(5)
            ->get();

        return view(
            "dashboards.privileged.index",
            compact("stats", "recentRentals", "lowStockItems"),
        );
    }
}
