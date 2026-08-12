<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RoomAndFacilityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }

    /**
     * Test Admin is allowed to perform Room and Facility actions.
     */
    public function test_admin_room_and_facility_actions_are_allowed(): void
    {
        // 1. Admin accessing list of rooms
        $response = $this->actingAs($this->admin)->get(route('admin.rooms.index'));
        $response->assertStatus(200);

        // 2. Admin creating facility
        $response = $this->actingAs($this->admin)->post(route('admin.facilities.store'), [
            'name' => 'Projector',
            'description' => 'HD Projector',
        ]);
        $response->assertRedirect(route('admin.facilities.index'));
        $this->assertDatabaseHas('facilities', ['name' => 'Projector']);
        
        $facility = Facility::first();

        // 3. Admin creating room with facility
        $response = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'name' => 'Boardroom',
            'location' => 'Level 2',
            'capacity' => 12,
            'description' => 'Executive Meeting Room',
            'status' => 'active',
            'facilities' => [$facility->id],
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Boardroom']);
        
        $room = Room::first();
        $this->assertTrue($room->facilities->contains($facility));

        // 4. Admin editing room
        $response = $this->actingAs($this->admin)->put(route('admin.rooms.update', $room), [
            'name' => 'Executive Boardroom',
            'location' => 'Level 2',
            'capacity' => 15,
            'description' => 'Executive Boardroom Room',
            'status' => 'inactive',
            'facilities' => [], // Unassign all
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Executive Boardroom', 'status' => 'inactive']);
        
        $room->refresh();
        $this->assertEquals(0, $room->facilities->count());

        // 5. Admin editing facility
        $response = $this->actingAs($this->admin)->put(route('admin.facilities.update', $facility), [
            'name' => 'Super Projector',
            'description' => 'Super HD Projector',
        ]);
        $response->assertRedirect(route('admin.facilities.index'));
        $this->assertDatabaseHas('facilities', ['name' => 'Super Projector']);
    }

    /**
     * Test User is denied from Room and Facility actions.
     */
    public function test_user_room_and_facility_actions_are_denied(): void
    {
        $room = Room::create([
            'name' => 'Room A',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $facility = Facility::create([
            'name' => 'Whiteboard',
            'description' => 'Papan tulis',
        ]);

        // User tries to view admin room list
        $response = $this->actingAs($this->user)->get(route('admin.rooms.index'));
        $response->assertStatus(403);

        // User tries to create room
        $response = $this->actingAs($this->user)->post(route('admin.rooms.store'), [
            'name' => 'Room Hack',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);
        $response->assertStatus(403);

        // User tries to edit room
        $response = $this->actingAs($this->user)->put(route('admin.rooms.update', $room), [
            'name' => 'Room Hack Edit',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'inactive',
        ]);
        $response->assertStatus(403);

        // User tries to view admin facility list
        $response = $this->actingAs($this->user)->get(route('admin.facilities.index'));
        $response->assertStatus(403);

        // User tries to create facility
        $response = $this->actingAs($this->user)->post(route('admin.facilities.store'), [
            'name' => 'Facility Hack',
        ]);
        $response->assertStatus(403);

        // User tries to edit facility
        $response = $this->actingAs($this->user)->put(route('admin.facilities.update', $facility), [
            'name' => 'Facility Hack Edit',
        ]);
        $response->assertStatus(403);

        // User tries to delete facility
        $response = $this->actingAs($this->user)->delete(route('admin.facilities.destroy', $facility));
        $response->assertStatus(403);
    }

    /**
     * Test Room-Facility Pivot Synchronization.
     */
    public function test_room_facility_pivot_synchronization(): void
    {
        // Setup facilities
        $fac1 = Facility::create(['name' => 'Facility 1']);
        $fac2 = Facility::create(['name' => 'Facility 2']);
        $fac3 = Facility::create(['name' => 'Facility 3']);

        // Create Room A with Facility 1 and Facility 2
        $roomA = Room::create([
            'name' => 'Room A',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);
        $roomA->facilities()->attach([$fac1->id, $fac2->id]);

        $this->assertTrue($roomA->facilities->contains($fac1));
        $this->assertTrue($roomA->facilities->contains($fac2));
        $this->assertFalse($roomA->facilities->contains($fac3));

        // Update Room A to have Facility 2 and Facility 3 via controller
        $response = $this->actingAs($this->admin)->put(route('admin.rooms.update', $roomA), [
            'name' => 'Room A',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
            'facilities' => [$fac2->id, $fac3->id],
        ]);

        $response->assertRedirect(route('admin.rooms.index'));

        // Refresh and check facilities
        $roomA->refresh();
        $roomFacilities = $roomA->facilities;

        $this->assertFalse($roomFacilities->contains($fac1), 'Facility 1 should be removed.');
        $this->assertTrue($roomFacilities->contains($fac2), 'Facility 2 should remain.');
        $this->assertTrue($roomFacilities->contains($fac3), 'Facility 3 should be added.');
        $this->assertEquals(2, $roomFacilities->count());
    }
}
