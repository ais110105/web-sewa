<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->hasPermissionTo("view-dashboard-privilege")) {
                return redirect()->route("dashboard");
            }

            return redirect()->route("home");
        }

        $categories = Category::select("id", "name", "description")
            ->withCount([
                "items as available_items_count" => function ($query) {
                    $query->where("status", "available");
                },
            ])
            ->orderByDesc("available_items_count")
            ->take(6)
            ->get();

        $featuredItems = Item::with("category")
            ->where("status", "available")
            ->orderByDesc("available_stock")
            ->latest()
            ->take(9)
            ->get();

        $banners = Item::with("category")
            ->where("status", "available")
            ->latest()
            ->take(6)
            ->get();

        $newArrivals = Item::with("category")
            ->where("status", "available")
            ->latest()
            ->take(10)
            ->get();

        $bestSellers = Item::with("category")
            ->where("status", "available")
            ->orderByDesc("available_stock")
            ->take(10)
            ->get();

        $relatedItems = Item::with("category")
            ->where("status", "available")
            ->inRandomOrder()
            ->take(8)
            ->get();

        $stats = [
            "items" => Item::count(),
            "availableStock" => (int) Item::sum("available_stock"),
            "completedRentals" => Rental::where("status", "completed")->count(),
        ];

        return view(
            "landing.home",
            compact(
                "categories",
                "featuredItems",
                "banners",
                "newArrivals",
                "bestSellers",
                "relatedItems",
                "stats",
            ),
        );
    }
}
