<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * Create a new booking request within a database transaction.
     */
    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            // Lock room row for concurrency protection
            $room = Room::where('id', $data['room_id'])->lockForUpdate()->firstOrFail();

            if (!$room->isActive()) {
                throw new Exception('Selected room is inactive and cannot be booked.');
            }

            // Business rule: Start time must be before end time
            if ($data['start_time'] >= $data['end_time']) {
                throw new Exception('Start time must be before end time.');
            }

            // Business rule: Booking date/time cannot be in the past
            $today = date('Y-m-d');
            if ($data['booking_date'] < $today) {
                throw new Exception('Booking date cannot be in the past.');
            }
            if ($data['booking_date'] === $today) {
                $currentTime = date('H:i:s');
                $startTimeFormatted = date('H:i:s', strtotime($data['start_time']));
                if ($startTimeFormatted < $currentTime) {
                    throw new Exception('Booking start time cannot be in the past.');
                }
            }

            // Business rule: Operating hours = 08:00 - 17:00
            $startTimeOnly = date('H:i', strtotime($data['start_time']));
            $endTimeOnly = date('H:i', strtotime($data['end_time']));
            if ($startTimeOnly < '08:00' || $endTimeOnly > '17:00') {
                throw new Exception('Booking must be within operating hours (08:00–17:00).');
            }

            // Business rule: Minimum duration = 1 hour
            $start = strtotime($data['start_time']);
            $end = strtotime($data['end_time']);
            if (($end - $start) < 3600) {
                throw new Exception('Minimum booking duration is 1 hour.');
            }

            // Stage 3: Final availability check
            $isAvailable = $this->availabilityService->isRoomAvailable(
                $room->id,
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (!$isAvailable) {
                throw new Exception('Room is no longer available for the selected time slot.');
            }

            return Booking::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Approve a pending booking.
     */
    public function approveBooking(Booking $booking, User $admin): Booking
    {
        if ($booking->status !== 'pending') {
            throw new Exception('Only pending bookings can be approved.');
        }

        return DB::transaction(function () use ($booking, $admin) {
            // Lock booking's room
            Room::where('id', $booking->room_id)->lockForUpdate()->firstOrFail();

            // Re-verify availability against approved bookings only
            $isAvailable = $this->availabilityService->isRoomAvailable(
                $booking->room_id,
                $booking->booking_date->format('Y-m-d'),
                $booking->start_time,
                $booking->end_time,
                $booking->id,
                ['approved']
            );

            if (!$isAvailable) {
                throw new Exception('Booking cannot be approved because of a schedule conflict.');
            }

            $booking->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            return $booking;
        });
    }

    /**
     * Reject a pending booking.
     */
    public function rejectBooking(Booking $booking, User $admin, string $reason): Booking
    {
        if ($booking->status !== 'pending') {
            throw new Exception('Only pending bookings can be rejected.');
        }

        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $booking;
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, User $user): Booking
    {
        if ($booking->user_id !== $user->id) {
            throw new Exception('You are not authorized to cancel this booking.');
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            throw new Exception('This booking cannot be cancelled in its current status.');
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return $booking;
    }
}
