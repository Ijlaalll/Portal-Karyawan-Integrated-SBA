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
        \Illuminate\Support\Facades\Storage::fake('public');

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

        // 1b. Admin accessing room create form
        $response = $this->actingAs($this->admin)->get(route('admin.rooms.create'));
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
        $roomImage = $this->createFakeImage('boardroom.png');
        $response = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'name' => 'Boardroom',
            'location' => 'Level 2',
            'capacity' => 12,
            'description' => 'Executive Meeting Room',
            'status' => 'active',
            'facilities' => [$facility->id],
            'image' => $roomImage,
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Boardroom']);
        
        $room = Room::first();
        $this->assertTrue($room->facilities->contains($facility));

        // 3b. Admin accessing room show & edit forms
        $this->actingAs($this->admin)->get(route('admin.rooms.show', $room))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.rooms.edit', $room))->assertStatus(200);

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

    /**
     * Test GA is allowed to perform Room and Facility actions when having meeting room access.
     */
    public function test_ga_room_and_facility_actions_are_allowed(): void
    {
        $gaUser = User::create([
            'name' => 'GA User',
            'email' => 'ga@example.com',
            'password' => Hash::make('password'),
            'role' => 'ga',
        ]);
        $gaUser->modules()->create(['module' => 'meeting_room']);

        // 1. GA accessing list of rooms
        $response = $this->actingAs($gaUser)->get(route('admin.rooms.index'));
        $response->assertStatus(200);

        // 2. GA creating facility
        $response = $this->actingAs($gaUser)->post(route('admin.facilities.store'), [
            'name' => 'Projector GA',
            'description' => 'GA HD Projector',
        ]);
        $response->assertRedirect(route('admin.facilities.index'));
        $this->assertDatabaseHas('facilities', ['name' => 'Projector GA']);
        
        $facility = Facility::where('name', 'Projector GA')->first();

        // 3. GA creating room with facility
        $gaRoomImage = $this->createFakeImage('boardroom_ga.png');
        $response = $this->actingAs($gaUser)->post(route('admin.rooms.store'), [
            'name' => 'Boardroom GA',
            'location' => 'Level 3',
            'capacity' => 10,
            'description' => 'GA Meeting Room',
            'status' => 'active',
            'facilities' => [$facility->id],
            'image' => $gaRoomImage,
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Boardroom GA']);
        
        $room = Room::where('name', 'Boardroom GA')->first();
        $this->assertTrue($room->facilities->contains($facility));

        // 4. GA editing room
        $response = $this->actingAs($gaUser)->put(route('admin.rooms.update', $room), [
            'name' => 'Executive Boardroom GA',
            'location' => 'Level 3',
            'capacity' => 12,
            'description' => 'GA Meeting Room Edited',
            'status' => 'inactive',
            'facilities' => [], // Unassign all
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Executive Boardroom GA', 'status' => 'inactive']);
        
        $room->refresh();
        $this->assertEquals(0, $room->facilities->count());
    }

    /**
     * Test GA without meeting_room module access is denied from Room and Facility actions.
     */
    public function test_ga_without_meeting_room_module_access_is_denied(): void
    {
        $gaUserNoMR = User::create([
            'name' => 'GA User No MR',
            'email' => 'ga_no_mr@example.com',
            'password' => Hash::make('password'),
            'role' => 'ga',
        ]);
        $gaUserNoMR->modules()->create(['module' => 'atk']);

        // GA tries to view admin room list
        $response = $this->actingAs($gaUserNoMR)->get(route('admin.rooms.index'));
        $response->assertStatus(403);

        // GA tries to create room
        $response = $this->actingAs($gaUserNoMR)->post(route('admin.rooms.store'), [
            'name' => 'Room Hack GA',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);
        $response->assertStatus(403);
    }

    protected function createFakeImage(string $filename = 'room.png'): \Illuminate\Http\UploadedFile
    {
        $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return \Illuminate\Http\UploadedFile::fake()->createWithContent($filename, $pngContent);
    }

    /**
     * Test Admin and GA can upload room image.
     */
    public function test_admin_and_ga_can_upload_room_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = $this->createFakeImage('meeting_room_a.png');

        $response = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'name' => 'Ruang Akasia',
            'location' => 'Lantai 2',
            'capacity' => 10,
            'description' => 'Ruangan ber-AC dan proyektor',
            'status' => 'active',
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Ruang Akasia']);

        $room = Room::where('name', 'Ruang Akasia')->first();
        $this->assertNotNull($room->image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($room->image);
        $this->assertStringContainsString('storage/', (string) $room->image_url);
    }

    /**
     * Test Admin can update room image and old image is deleted.
     */
    public function test_admin_can_update_room_image_and_old_image_is_deleted(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $oldFile = $this->createFakeImage('old_room.png');
        $room = app(\App\Services\RoomService::class)->createRoom([
            'name' => 'Ruang Cempaka',
            'location' => 'Lantai 1',
            'capacity' => 8,
            'status' => 'active',
            'image' => $oldFile,
        ]);

        $oldImagePath = $room->image;
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($oldImagePath);

        $newFile = $this->createFakeImage('new_room.png');
        $response = $this->actingAs($this->admin)->put(route('admin.rooms.update', $room), [
            'name' => 'Ruang Cempaka Baru',
            'location' => 'Lantai 1',
            'capacity' => 8,
            'status' => 'active',
            'image' => $newFile,
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldImagePath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($room->fresh()->image);
    }

    /**
     * Test Admin can remove room image.
     */
    public function test_admin_can_remove_room_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = $this->createFakeImage('room_to_remove.png');
        $room = app(\App\Services\RoomService::class)->createRoom([
            'name' => 'Ruang Flamboyan',
            'location' => 'Lantai 3',
            'capacity' => 15,
            'status' => 'active',
            'image' => $file,
        ]);

        $imagePath = $room->image;
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($this->admin)->put(route('admin.rooms.update', $room), [
            'name' => 'Ruang Flamboyan',
            'location' => 'Lantai 3',
            'capacity' => 15,
            'status' => 'active',
            'remove_image' => 1,
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($imagePath);
        $this->assertNull($room->fresh()->image);
    }

    /**
     * Test room image validation fails for invalid file.
     */
    public function test_room_image_validation_fails_for_invalid_file(): void
    {
        $invalidFile = \Illuminate\Http\UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'name' => 'Ruang Error',
            'location' => 'Lantai 1',
            'capacity' => 5,
            'status' => 'active',
            'image' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * Test room creation requires mandatory image.
     */
    public function test_room_creation_requires_mandatory_image(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'name' => 'Ruang Tanpa Foto',
            'location' => 'Lantai 1',
            'capacity' => 8,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['image']);
    }
}
