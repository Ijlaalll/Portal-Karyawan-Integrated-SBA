<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomService
{
    /**
     * Create a new room with facilities inside a transaction.
     */
    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $room = Room::create([
                'name' => $data['name'],
                'location' => $data['location'],
                'capacity' => $data['capacity'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'image' => $data['image'] ?? null,
            ]);

            if (isset($data['facilities'])) {
                $room->facilities()->sync($data['facilities']);
            }

            return $room;
        });
    }

    /**
     * Update a room and sync facilities inside a transaction.
     */
    public function updateRoom(Room $room, array $data): Room
    {
        return DB::transaction(function () use ($room, $data) {
            $room->update([
                'name' => $data['name'],
                'location' => $data['location'],
                'capacity' => $data['capacity'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'image' => $data['image'] ?? null,
            ]);

            if (isset($data['facilities'])) {
                $room->facilities()->sync($data['facilities']);
            } else {
                $room->facilities()->sync([]);
            }

            return $room;
        });
    }

    /**
     * Change room status.
     */
    public function changeStatus(Room $room, string $status): Room
    {
        $room->update(['status' => $status]);
        return $room;
    }
}
