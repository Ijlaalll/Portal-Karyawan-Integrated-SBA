<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Display a listing of the user's bookings.
     */
    public function index()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with('room')
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request)
    {
        $rooms = Room::where('status', 'active')->get();
        $selectedRoomId = $request->query('room_id');
        $date = $request->query('date');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');

        return view('bookings.create', compact('rooms', 'selectedRoomId', 'date', 'startTime', 'endTime'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking(
                auth()->user(),
                $request->validated()
            );

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'Booking requested successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified booking details.
     */
    public function show(Booking $booking)
    {
        Gate::authorize('view', $booking);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        Gate::authorize('cancel', $booking);

        try {
            $this->bookingService->cancelBooking($booking, auth()->user());

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
