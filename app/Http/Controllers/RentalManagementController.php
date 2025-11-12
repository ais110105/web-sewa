<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalManagementController extends Controller
{
    /**
     * Display all rentals for admin management
     */
    public function index(Request $request)
    {
        $query = Rental::with(['user', 'rentalItems.item.category', 'transaction'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by rental code or user name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rental_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rentals = $query->paginate(15);

        return view('rentals.management.index', compact('rentals'));
    }

    /**
     * Display rental detail
     */
    public function show(Rental $rental)
    {
        $rental->load(['user', 'rentalItems.item.category', 'transaction']);
        return view('rentals.management.show', compact('rental'));
    }

    /**
     * Update rental status
     */
    public function updateStatus(Request $request, Rental $rental)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,on_rent,completed,cancelled',
        ]);

        $oldStatus = $rental->status;
        $rental->update(['status' => $request->status]);

        // If status changed to on_rent, update picked_up_at
        if ($request->status === 'on_rent' && $oldStatus !== 'on_rent') {
            $rental->update(['picked_up_at' => now()]);
        }

        // If status changed to completed, mark as returned and increase stock
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $rental->markAsReturned();
        }

        return redirect()->back()->with('success', 'Rental status updated successfully');
    }

    /**
     * Mark rental as returned
     */
    public function markAsReturned(Rental $rental)
    {
        if ($rental->markAsReturned()) {
            return redirect()->back()->with('success', 'Rental marked as returned and stock updated');
        }

        return redirect()->back()->with('error', 'Rental cannot be marked as returned');
    }

    /**
     * Get rental statistics
     */
    public function statistics()
    {
        $stats = [
            'total_rentals' => Rental::count(),
            'pending' => Rental::where('status', 'pending')->count(),
            'confirmed' => Rental::where('status', 'confirmed')->count(),
            'on_rent' => Rental::where('status', 'on_rent')->count(),
            'completed' => Rental::where('status', 'completed')->count(),
            'cancelled' => Rental::where('status', 'cancelled')->count(),
            'unpaid' => Rental::where('payment_status', 'unpaid')->count(),
            'paid' => Rental::where('payment_status', 'paid')->count(),
            'total_revenue' => Rental::where('payment_status', 'paid')->sum('total_price'),
        ];

        return response()->json($stats);
    }
}
