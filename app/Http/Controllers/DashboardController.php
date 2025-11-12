<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get rental statistics for the current user
        $totalRentals = Rental::where('user_id', $user->id)->count();
        $confirmedRentals = Rental::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->count();
        $onRentRentals = Rental::where('user_id', $user->id)
            ->where('status', 'on_rent')
            ->count();
        $completedRentals = Rental::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('dashboards.standard.index', compact(
            'totalRentals',
            'confirmedRentals',
            'onRentRentals',
            'completedRentals'
        ));
    }
}
