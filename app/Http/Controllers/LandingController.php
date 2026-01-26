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

        // Fetch categories with count (1 query)
        $categories = Category::select("id", "name", "description")
            ->withCount([
                "items as available_items_count" => function ($query) {
                    $query->where("status", "available");
                },
            ])
            ->orderByDesc("available_items_count")
            ->take(6)
            ->get();

        // Fetch all available items once with eager loading (1 query)
        $availableItems = Item::with("category:id,name")
            ->where("status", "available")
            ->latest()
            ->take(20)
            ->get();

        // Featured items - sorted by available stock (reuse collection)
        $featuredItems = $availableItems->sortByDesc("available_stock")->take(9)->values();

        // Banners - latest 6 (reuse collection)
        $banners = $availableItems->take(6);

        // New arrivals - latest 10 (reuse collection)
        $newArrivals = $availableItems->take(10);

        // Best sellers - top 10 by stock (reuse collection)
        $bestSellers = $availableItems->sortByDesc("available_stock")->take(10)->values();

        // Related items - random 8 (reuse collection)
        $relatedItems = $availableItems->shuffle()->take(8);

        // Combine stats into a single optimized query using raw SQL
        $statsRaw = \DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM items) as items_count,
                (SELECT COALESCE(SUM(available_stock), 0) FROM items) as available_stock,
                (SELECT COUNT(*) FROM rentals WHERE status = 'completed') as completed_rentals
        ");

        $stats = [
            "items" => $statsRaw->items_count,
            "availableStock" => (int) $statsRaw->available_stock,
            "completedRentals" => $statsRaw->completed_rentals,
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
