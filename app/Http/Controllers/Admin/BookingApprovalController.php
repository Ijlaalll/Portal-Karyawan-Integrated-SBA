<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingApprovalController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Display a listing of pending bookings for approval.
     */
    public function index()
    {
        $bookings = Booking::with(['user', 'room'])
            ->where('status', 'pending')
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        return view('admin.approvals.index', compact('bookings'));
    }

    /**
     * Approve a pending booking.
     */
    public function approve(Booking $booking)
    {
        try {
            $this->bookingService->approveBooking($booking, auth()->user());

            return redirect()
                ->route('admin.approvals.index')
                ->with('success', 'Booking approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a pending booking.
     */
    public function reject(Request $request, Booking $booking)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->bookingService->rejectBooking(
                $booking,
                auth()->user(),
                $request->input('rejection_reason')
            );

            return redirect()
                ->route('admin.approvals.index')
                ->with('success', 'Booking rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
