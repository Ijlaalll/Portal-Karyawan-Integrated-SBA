<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Facility;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all Eloquent relationships are correctly defined and queryable.
     */
    public function test_eloquent_relationships(): void
    {
        // 1. Create entities
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user_test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $room = Room::create([
            'name' => 'Test Room',
            'location' => 'Building 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $facility = Facility::create([
            'name' => 'Projector',
            'description' => 'HD Projector',
        ]);

        // Attach facility to room
        $room->facilities()->attach($facility);

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'approved_by' => $admin->id,
            'title' => 'Test Meeting',
            'description' => 'Test Description',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Refresh model relations
        $user->load('bookings');
        $admin->load('approvedBookings');
        $room->load(['bookings', 'facilities']);
        $facility->load('rooms');

        // 2. Assert relationships
        // User -> bookings
        $this->assertTrue($user->bookings->contains($booking));
        $this->assertEquals(1, $user->bookings->count());

        // User -> approvedBookings (as approver)
        $this->assertTrue($admin->approvedBookings->contains($booking));
        $this->assertEquals(1, $admin->approvedBookings->count());

        // Booking -> user
        $this->assertEquals($user->id, $booking->user->id);

        // Booking -> room
        $this->assertEquals($room->id, $booking->room->id);

        // Booking -> approver
        $this->assertEquals($admin->id, $booking->approver->id);

        // Room -> bookings
        $this->assertTrue($room->bookings->contains($booking));
        $this->assertEquals(1, $room->bookings->count());

        // Room -> facilities
        $this->assertTrue($room->facilities->contains($facility));

        // Facility -> rooms
        $this->assertTrue($facility->rooms->contains($room));
    }
}
