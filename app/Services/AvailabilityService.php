<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class AvailabilityService
{
    /**
     * Check if a specific room is available for a given date and time range.
     */
    public function isRoomAvailable(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null,
        array $statuses = ['pending', 'approved']
    ): bool {
        $query = Booking::where('room_id', $roomId)
            ->whereDate('booking_date', $date)
            ->whereIn('status', $statuses)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Find all active rooms available for given criteria.
     */
    public function findAvailableRooms(string $date, string $startTime, string $endTime, int $minCapacity = 1): Collection
    {
        return Room::where('status', 'active')
            ->where('capacity', '>=', $minCapacity)
            ->whereDoesntHave('bookings', function ($query) use ($date, $startTime, $endTime) {
                $query->whereDate('booking_date', $date)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->with('facilities')
            ->get();
    }
}
