<?php

namespace App\Http\Controllers;

use App\Services\AvailabilityService;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * Display the room availability calendar.
     */
    public function index(Request $request)
    {
        Booking::syncCompletedBookings();
        $date = $request->query('date', date('Y-m-d'));
        $roomId = $request->query('room_id');
        $status = $request->query('status');

        // Fetch rooms to display in rows
        $roomsQuery = Room::where('status', 'active');
        if ($roomId) {
            $roomsQuery->where('id', $roomId);
        }
        $rooms = $roomsQuery->get();

        // Fetch bookings for the selected date
        $bookingsQuery = Booking::whereDate('booking_date', $date)
            ->with(['user', 'room']);

        if ($status) {
            $bookingsQuery->where('status', $status);
        } else {
            // Show statuses that affect availability + completed
            $bookingsQuery->whereIn('status', ['pending', 'approved', 'completed']);
        }

        $bookings = $bookingsQuery->get();

        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Map bookings to timeline segments with server-side privacy filtering
        $mappedBookings = $bookings->map(function ($booking) use ($user, $isAdmin) {
            $isOwner = $booking->user_id === $user->id;

            // Operating Hours: 08:00 - 17:00 (540 minutes total)
            $startSec = strtotime($booking->start_time);
            $endSec = strtotime($booking->end_time);
            $dayStartSec = strtotime('08:00');

            $startMinutes = ($startSec - $dayStartSec) / 60;
            $durationMinutes = ($endSec - $startSec) / 60;

            // Clip within 08:00 - 17:00
            $startMinutes = max(0, min(540, $startMinutes));
            $durationMinutes = max(0, min(540 - $startMinutes, $durationMinutes));

            $left = ($startMinutes / 540) * 100;
            $width = ($durationMinutes / 540) * 100;

            return [
                'id' => $booking->id,
                'room_id' => $booking->room_id,
                'left' => $left,
                'width' => $width,
                'status' => $booking->status,
                'start_time' => date('H:i', $startSec),
                'end_time' => date('H:i', $endSec),
                'is_owner' => $isOwner,
                'title' => ($isAdmin || $isOwner) ? $booking->title : 'Occupied',
                'user_name' => ($isAdmin || $isOwner) ? $booking->user->name : 'Confidential',
                'description' => ($isAdmin || $isOwner) ? $booking->description : null,
            ];
        });

        // Get all active rooms for the drop-down filter
        $allRooms = Room::where('status', 'active')->get();

        return view('calendar.index', compact('rooms', 'mappedBookings', 'date', 'roomId', 'status', 'allRooms'));
    }
}
