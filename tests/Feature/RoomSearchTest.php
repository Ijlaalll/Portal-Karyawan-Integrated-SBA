<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RoomSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Room $room1;
    protected Room $room2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->room1 = Room::create([
            'name' => 'Alpha Boardroom',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->room2 = Room::create([
            'name' => 'Beta Huddle',
            'location' => 'Lantai 2',
            'capacity' => 4,
            'status' => 'active',
        ]);
    }

    /**
     * Test search displays all active rooms by default without parameters.
     */
    public function test_default_search_lists_all_active_rooms(): void
    {
        $response = $this->actingAs($this->user)->get(route('rooms.search'));
        $response->assertStatus(200);
        $response->assertViewHas('rooms');
        
        $rooms = $response->viewData('rooms');
        $this->assertEquals(2, $rooms->count());
    }

    /**
     * Test searching by date and capacity.
     */
    public function test_searching_by_capacity_filters_rooms(): void
    {
        // Search for min capacity of 6 pax
        $response = $this->actingAs($this->user)->get(route('rooms.search', [
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'capacity' => 6,
        ]));

        $response->assertStatus(200);
        $rooms = $response->viewData('rooms');
        
        // Only Room 1 (capacity 10) fits, Room 2 (capacity 4) is excluded
        $this->assertEquals(1, $rooms->count());
        $this->assertEquals($this->room1->id, $rooms->first()->id);
    }

    /**
     * Test searching excludes rooms with overlapping approved/pending bookings.
     */
    public function test_searching_excludes_overlapping_rooms(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create approved booking on Room 1 (10:00 - 12:00)
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room1->id,
            'title' => 'Meeting 1',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'approved',
        ]);

        // Search for slot (11:00 - 12:00)
        $response = $this->actingAs($this->user)->get(route('rooms.search', [
            'date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'capacity' => 1,
        ]));

        $response->assertStatus(200);
        $rooms = $response->viewData('rooms');

        // Room 1 should be excluded due to overlap, leaving only Room 2
        $this->assertEquals(1, $rooms->count());
        $this->assertEquals($this->room2->id, $rooms->first()->id);
    }
}
