<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use App\Services\AuditLogService;
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
        Booking::syncCompletedBookings();

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
        Booking::syncCompletedBookings();

        $meetingType = $request->query('meeting_type', 'offline');
        $date = $request->query('date', date('Y-m-d'));
        $startTime = $request->query('start_time', '09:00');
        $endTime = $request->query('end_time', '10:00');
        $participantCount = (int) $request->query('participant_count', 1);
        $selectedRoomId = $request->query('room_id');

        // Fetch rooms list
        $rooms = Room::where('status', 'active')->get();

        // Query bookings for selected date (for calendar display)
        $bookingsQuery = Booking::whereDate('booking_date', $date)
            ->with(['user', 'room'])
            ->whereIn('status', ['pending', 'approved', 'completed']);
        $bookings = $bookingsQuery->get();

        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Map bookings to timeline segments (for calendar view)
        $mappedBookings = $bookings->map(function ($booking) use ($user, $isAdmin) {
            $isOwner = $booking->user_id === $user->id;
            $startSec = strtotime($booking->start_time);
            $endSec = strtotime($booking->end_time);
            $dayStartSec = strtotime('08:00');

            $startMinutes = ($startSec - $dayStartSec) / 60;
            $durationMinutes = ($endSec - $startSec) / 60;

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

        // Find available rooms (meeting room offline/hybrid filtering)
        $availableRooms = collect();
        if (($meetingType === 'offline' || $meetingType === 'hybrid') && $date && $startTime && $endTime) {
            // Find rooms available for the range
            $availableRooms = Room::where('status', 'active')
                ->where('capacity', '>=', $participantCount)
                ->whereDoesntHave('bookings', function ($query) use ($date, $startTime, $endTime) {
                    $query->whereDate('booking_date', $date)
                        ->whereIn('status', ['pending', 'approved'])
                        ->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->with('facilities')
                ->get();
        }

        // Get all active rooms for the calendar rows
        $allRooms = Room::where('status', 'active')->get();

        return view('bookings.create', compact(
            'rooms',
            'availableRooms',
            'mappedBookings',
            'allRooms',
            'selectedRoomId',
            'date',
            'startTime',
            'endTime',
            'participantCount',
            'meetingType'
        ));
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

            AuditLogService::log(
                'meeting_room',
                'create_booking',
                "Pemesanan ruang '{$booking->room->name}' dibuat untuk tanggal " . \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y'),
                [
                    'booking_id' => $booking->id,
                    'room_id' => $booking->room_id,
                    'room_name' => $booking->room->name ?? null,
                    'booking_date' => (string) $booking->booking_date,
                    'time' => $booking->start_time . ' - ' . $booking->end_time,
                ],
                auth()->user()
            );

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'Pemesanan berhasil diajukan.');
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
        Booking::syncCompletedBookings();
        $booking = $booking->fresh();

        Gate::authorize('view', $booking);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        Booking::syncCompletedBookings();
        $booking = $booking->fresh();

        Gate::authorize('cancel', $booking);

        try {
            $this->bookingService->cancelBooking($booking, auth()->user());

            AuditLogService::log(
                'meeting_room',
                'cancel_booking',
                "Pemesanan ruang '{$booking->room->name}' dibatalkan oleh pengguna.",
                [
                    'booking_id' => $booking->id,
                    'room_id' => $booking->room_id,
                ],
                auth()->user()
            );

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'Pemesanan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store the review rating and feedback for a completed booking.
     */
    public function storeReview(Request $request, Booking $booking)
    {
        Booking::syncCompletedBookings();
        $booking = $booking->fresh();

        // Authorize that the user owns the booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Tindakan tidak sah.');
        }

        // Must be completed status
        if ($booking->status !== 'completed') {
            return back()->withErrors(['error' => 'Anda hanya dapat mengulas pemesanan yang telah selesai.']);
        }

        // Must not already have a review
        if ($booking->rating !== null) {
            return back()->withErrors(['error' => 'Anda sudah mengulas pemesanan ini.']);
        }

        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking->update([
            'rating' => $request->input('rating'),
            'feedback' => $request->input('feedback'),
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Terima kasih. Ulasan Anda telah dikirim.');
     }

    /**
     * Snooze the review modal for a specific booking.
     */
    public function snoozeReview(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $snoozed = session('snoozed_bookings', []);
        if (!in_array($booking->id, $snoozed)) {
            $snoozed[] = $booking->id;
        }
        session(['snoozed_bookings' => $snoozed]);

        return response()->json(['success' => true]);
    }

    /**
     * Acknowledge ready notification popup for a booking.
     */
    public function acknowledgeReady(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($booking->ga_status !== null) {
            $booking->update(['ga_ready_popup_acknowledged' => true]);
        } else {
            $booking->update(['ready_popup_acknowledged' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Acknowledge active meeting popup for an online booking.
     */
    public function acknowledgeActive(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($booking->it_status !== null) {
            $booking->update(['it_ready_popup_acknowledged' => true]);
        } else {
            $booking->update(['active_popup_acknowledged' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Acknowledge cancellation popup for a booking.
     */
    public function acknowledgeCancellation(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->update(['cancellation_popup_acknowledged' => true]);

        return response()->json(['success' => true]);
    }}
