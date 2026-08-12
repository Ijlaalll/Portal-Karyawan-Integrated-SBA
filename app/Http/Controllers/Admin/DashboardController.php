<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Room;
use App\Models\Facility;
use App\Models\Booking;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with administrative stats.
     */
    public function index()
    {
        $stats = [
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('status', 'active')->count(),
            'inactive_rooms' => Room::where('status', 'inactive')->count(),
            'total_facilities' => Facility::count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'approved_bookings' => Booking::where('status', 'approved')->count(),
            'rejected_bookings' => Booking::where('status', 'rejected')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
        ];

        $recentBookings = Booking::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentBookings'));
    }
}
