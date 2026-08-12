<?php

namespace App\Http\Controllers;

use App\Services\AvailabilityService;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomSearchController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * Search for available rooms.
     */
    public function index(Request $request)
    {
        $date = $request->query('date');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');
        $capacity = $request->query('capacity', 1);

        $rooms = collect();
        $searched = false;

        if ($date && $startTime && $endTime) {
            $request->validate([
                'date' => ['required', 'date', 'after_or_equal:today'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'capacity' => ['nullable', 'integer', 'min:1'],
            ]);

            $rooms = $this->availabilityService->findAvailableRooms(
                $date,
                $startTime,
                $endTime,
                (int)$capacity
            );
            $searched = true;
        } else {
            $rooms = Room::where('status', 'active')->with('facilities')->get();
        }

        return view('rooms.search', compact('rooms', 'searched', 'date', 'startTime', 'endTime', 'capacity'));
    }
}
