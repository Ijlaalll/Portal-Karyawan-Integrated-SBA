<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Booking;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with statistics and recent bookings.
     */
    public function index()
    {
        Booking::syncCompletedBookings();
        $userId = auth()->id();

        $stats = [
            'total' => Booking::where('user_id', $userId)->count(),
            'pending' => Booking::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => Booking::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected' => Booking::where('user_id', $userId)->where('status', 'rejected')->count(),
            'cancelled' => Booking::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'completed' => Booking::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        $recentBookings = Booking::where('user_id', $userId)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentBookings'));
    }
}
