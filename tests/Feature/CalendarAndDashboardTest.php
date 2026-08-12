<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CalendarAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $admin;
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

        $this->otherUser = User::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->room1 = Room::create([
            'name' => 'Room A',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->room2 = Room::create([
            'name' => 'Room B',
            'location' => 'Lantai 2',
            'capacity' => 12,
            'status' => 'active',
        ]);
    }

    /**
     * Test User Dashboard access and statistics.
     */
    public function test_user_dashboard_access_and_stats(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create booking for user
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room1->id,
            'title' => 'My Meeting',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        // Create booking for otherUser
        Booking::create([
            'user_id' => $this->otherUser->id,
            'room_id' => $this->room1->id,
            'title' => 'Other Meeting',
            'booking_date' => $date,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        // John should only count his own bookings
        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(0, $stats['approved']);
    }

    /**
     * Test Admin Dashboard access and statistics.
     */
    public function test_admin_dashboard_access_and_stats(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create booking for user
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room1->id,
            'title' => 'My Meeting',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        // Create booking for otherUser
        Booking::create([
            'user_id' => $this->otherUser->id,
            'room_id' => $this->room2->id,
            'title' => 'Other Meeting',
            'booking_date' => $date,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        // Admin should see total administrative counts across all users
        $this->assertEquals(2, $stats['total_bookings']);
        $this->assertEquals(1, $stats['pending_bookings']);
        $this->assertEquals(1, $stats['approved_bookings']);
        $this->assertEquals(2, $stats['total_rooms']);
    }

    /**
     * Test Dashboard Authorization.
     */
    public function test_dashboard_authorizations(): void
    {
        // User cannot access Admin dashboard
        $response = $this->actingAs($this->user)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Guest cannot access User dashboard
        Auth::logout();
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test Calendar privacy rules.
     */
    public function test_calendar_privacy_rules_for_normal_users(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // John's booking (owner)
        $bookingJohn = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room1->id,
            'title' => 'John\'s Agenda',
            'description' => 'John\'s Secret',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        // Alice's booking (other user)
        $bookingAlice = Booking::create([
            'user_id' => $this->otherUser->id,
            'room_id' => $this->room1->id,
            'title' => 'Alice\'s Agenda',
            'description' => 'Alice\'s Secret',
            'booking_date' => $date,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'status' => 'approved',
        ]);

        // John views calendar
        $response = $this->actingAs($this->user)->get(route('calendar', ['date' => $date]));
        $response->assertStatus(200);
        $response->assertViewHas('mappedBookings');

        $mapped = $response->viewData('mappedBookings');

        // Find John's mapped event
        $johnEvent = $mapped->firstWhere('id', $bookingJohn->id);
        $this->assertNotNull($johnEvent);
        $this->assertEquals('John\'s Agenda', $johnEvent['title']); // Visible because owner
        $this->assertEquals('John Doe', $johnEvent['user_name']);
        $this->assertEquals('John\'s Secret', $johnEvent['description']);

        // Find Alice's mapped event
        $aliceEvent = $mapped->firstWhere('id', $bookingAlice->id);
        $this->assertNotNull($aliceEvent);
        $this->assertEquals('Occupied', $aliceEvent['title']); // Hides original title
        $this->assertEquals('Confidential', $aliceEvent['user_name']); // Hides original user name
        $this->assertNull($aliceEvent['description']); // Hides description
    }

    /**
     * Test Calendar visibility for Admin.
     */
    public function test_calendar_full_visibility_for_admins(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $bookingAlice = Booking::create([
            'user_id' => $this->otherUser->id,
            'room_id' => $this->room1->id,
            'title' => 'Alice\'s Agenda',
            'description' => 'Alice\'s Secret',
            'booking_date' => $date,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'status' => 'approved',
        ]);

        // Admin views calendar
        $response = $this->actingAs($this->admin)->get(route('calendar', ['date' => $date]));
        $response->assertStatus(200);

        $mapped = $response->viewData('mappedBookings');
        $aliceEvent = $mapped->firstWhere('id', $bookingAlice->id);

        $this->assertNotNull($aliceEvent);
        $this->assertEquals('Alice\'s Agenda', $aliceEvent['title']); // Visible for admin
        $this->assertEquals('Alice Smith', $aliceEvent['user_name']);
        $this->assertEquals('Alice\'s Secret', $aliceEvent['description']);
    }

    /**
     * Test Room filtering on calendar.
     */
    public function test_calendar_room_filtering(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->user)->get(route('calendar', ['date' => $date, 'room_id' => $this->room1->id]));
        $response->assertStatus(200);
        $response->assertViewHas('rooms');
        
        $rooms = $response->viewData('rooms');
        // Should only load room 1 row
        $this->assertEquals(1, $rooms->count());
        $this->assertEquals($this->room1->id, $rooms->first()->id);
    }
}
