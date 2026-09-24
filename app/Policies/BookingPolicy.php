<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view the booking detail.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can cancel the booking.
     *
     * User can cancel only if all required teams are still in 'pending' status.
     * Once any required team has advanced beyond pending (in_progress, ready, not_ready),
     * cancellation is locked to protect the team's work.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->id !== $booking->user_id) {
            return false;
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return false;
        }

        $startDateTime = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time, 'Asia/Jakarta');
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        if ($now->gte($startDateTime)) {
            return false;
        }

        // Preparation lock: block cancellation if any required team has advanced beyond pending
        if ($booking->status === 'approved') {
            if ($booking->meeting_type === 'offline') {
                // GA required — block if ga_status is not pending
                if ($booking->ga_status !== null && $booking->ga_status !== 'pending') {
                    return false;
                }
                // Legacy flow
                if ($booking->ga_status === null && $booking->preparation_status !== null
                    && $booking->preparation_status !== 'Menunggu Persiapan') {
                    return false;
                }
            } elseif ($booking->meeting_type === 'online') {
                // Online bookings with approved status are not cancellable (existing rule)
                return false;
            } elseif ($booking->meeting_type === 'hybrid') {
                // Both GA and IT required — block if either has advanced beyond pending
                if ($booking->ga_status !== null && $booking->ga_status !== 'pending') {
                    return false;
                }
                if ($booking->it_status !== null && $booking->it_status !== 'pending') {
                    return false;
                }
            }
        }

        return true;
    }
}

