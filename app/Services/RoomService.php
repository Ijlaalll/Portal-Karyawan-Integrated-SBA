<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomService
{
    /**
     * Create a new room with facilities inside a transaction.
     */
    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $data['image']->store('rooms', 'public');
            }

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
            // Handle image removal request
            if (!empty($data['remove_image'])) {
                if ($room->image && Storage::disk('public')->exists($room->image)) {
                    Storage::disk('public')->delete($room->image);
                }
                $data['image'] = null;
            } elseif (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                // Delete old image if exists
                if ($room->image && Storage::disk('public')->exists($room->image)) {
                    Storage::disk('public')->delete($room->image);
                }
                $data['image'] = $data['image']->store('rooms', 'public');
            } else {
                unset($data['image']);
            }

            unset($data['remove_image']);

            $updateData = [
                'name' => $data['name'],
                'location' => $data['location'],
                'capacity' => $data['capacity'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
            ];

            if (array_key_exists('image', $data)) {
                $updateData['image'] = $data['image'];
            }

            $room->update($updateData);

            if (isset($data['facilities'])) {
                $room->facilities()->sync($data['facilities']);
            } else {
                $room->facilities()->sync([]);
            }

            return $room;
        });
    }

    /**
     * Delete a room and clean up its stored image.
     */
    public function deleteRoom(Room $room): void
    {
        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }
        $room->delete();
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
