<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Exception;

class BookingAndAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Room $activeRoom;
    protected Room $inactiveRoom;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->activeRoom = Room::create([
            'name' => 'Room A',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->inactiveRoom = Room::create([
            'name' => 'Room B',
            'location' => 'Lantai 2',
            'capacity' => 8,
            'status' => 'inactive',
        ]);

        $this->bookingService = app(BookingService::class);
    }

    /**
     * Active room + free slot -> SUCCESS
     */
    public function test_booking_active_room_with_free_slot_succeeds(): void
    {
        $data = [
            'room_id' => $this->activeRoom->id,
            'title' => 'Weekly Sync',
            'description' => 'Meeting description',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
        ];

        $booking = $this->bookingService->createBooking($this->user, $data);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Active room + overlapping pending -> FAIL
     */
    public function test_booking_overlapping_with_pending_booking_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create initial pending booking (10:00 - 12:00)
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Existing Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'pending',
        ]);

        // Attempt overlapping booking (11:00 - 13:00)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Room is no longer available for the selected time slot.');

        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'New Booking',
            'booking_date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
        ]);
    }

    /**
     * Active room + overlapping approved -> FAIL
     */
    public function test_booking_overlapping_with_approved_booking_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create approved booking (10:00 - 12:00)
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Approved Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        // Attempt overlap
        $this->expectException(Exception::class);
        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'New Booking',
            'booking_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);
    }

    /**
     * Active room + overlapping rejected -> SUCCESS
     */
    public function test_booking_overlapping_with_rejected_booking_succeeds(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Rejected Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'rejected',
        ]);

        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'New Booking',
            'booking_date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Active room + overlapping cancelled -> SUCCESS
     */
    public function test_booking_overlapping_with_cancelled_booking_succeeds(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Cancelled Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'cancelled',
        ]);

        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'New Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Active room + overlapping completed -> SUCCESS
     */
    public function test_booking_overlapping_with_completed_booking_succeeds(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Completed Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'completed',
        ]);

        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'New Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Boundary end == start -> SUCCESS
     */
    public function test_boundary_booking_end_equals_start_succeeds(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'First Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'Second Booking',
            'booking_date' => $date,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Start == end -> FAIL
     */
    public function test_booking_with_same_start_and_end_time_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Start time must be before end time.');

        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'Zero Duration Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:00',
        ]);
    }

    /**
     * Start > end -> FAIL
     */
    public function test_booking_with_start_time_after_end_time_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Start time must be before end time.');

        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'Invalid Time Booking',
            'booking_date' => $date,
            'start_time' => '11:00',
            'end_time' => '10:00',
        ]);
    }

    /**
     * Inactive room -> FAIL
     */
    public function test_booking_inactive_room_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Selected room is inactive and cannot be booked.');

        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->inactiveRoom->id,
            'title' => 'Inactive Room Booking',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
    }

    /**
     * Different room -> SUCCESS
     */
    public function test_overlapping_time_on_different_rooms_succeeds(): void
    {
        $date = now()->addDay()->format('Y-m-d');
        $anotherActiveRoom = Room::create([
            'name' => 'Room C',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        // Booking on activeRoom (10:00 - 12:00)
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Booking Room A',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        // Booking on anotherActiveRoom (10:00 - 11:00)
        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $anotherActiveRoom->id,
            'title' => 'Booking Room C',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Different date -> SUCCESS
     */
    public function test_overlapping_time_on_different_dates_succeeds(): void
    {
        $date1 = now()->addDay()->format('Y-m-d');
        $date2 = now()->addDays(2)->format('Y-m-d');

        // Booking on Date 1 (10:00 - 12:00)
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'Booking Day 1',
            'booking_date' => $date1,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        // Booking on Date 2 (10:00 - 11:00)
        $booking = $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'Booking Day 2',
            'booking_date' => $date2,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /**
     * Operating hours validation -> FAIL
     */
    public function test_booking_outside_operating_hours_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Attempt booking before operating hours (07:00 - 08:30)
        try {
            $this->bookingService->createBooking($this->user, [
                'room_id' => $this->activeRoom->id,
                'title' => 'Early Meeting',
                'booking_date' => $date,
                'start_time' => '07:00',
                'end_time' => '08:30',
            ]);
            $this->fail('Booking before 08:00 should fail.');
        } catch (Exception $e) {
            $this->assertEquals('Booking must be within operating hours (08:00–17:00).', $e->getMessage());
        }

        // Attempt booking after operating hours (16:30 - 17:30)
        try {
            $this->bookingService->createBooking($this->user, [
                'room_id' => $this->activeRoom->id,
                'title' => 'Late Meeting',
                'booking_date' => $date,
                'start_time' => '16:30',
                'end_time' => '17:30',
            ]);
            $this->fail('Booking after 17:00 should fail.');
        } catch (Exception $e) {
            $this->assertEquals('Booking must be within operating hours (08:00–17:00).', $e->getMessage());
        }
    }

    /**
     * Duration < 1 hour -> FAIL
     */
    public function test_booking_duration_less_than_one_hour_fails(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Minimum booking duration is 1 hour.');

        $this->bookingService->createBooking($this->user, [
            'room_id' => $this->activeRoom->id,
            'title' => 'Short Sync',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:45',
        ]);
    }

    /**
     * Booking ownership and authorization verification.
     */
    public function test_booking_details_ownership_authorization(): void
    {
        $date = now()->addDay()->format('Y-m-d');
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->activeRoom->id,
            'title' => 'John\'s Meeting',
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'pending',
        ]);

        $anotherUser = User::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // John (owner) can view details
        $response = $this->actingAs($this->user)->get(route('bookings.show', $booking));
        $response->assertStatus(200);

        // Admin can view details
        $response = $this->actingAs($this->admin)->get(route('bookings.show', $booking));
        $response->assertStatus(200);

        // Alice (non-owner, non-admin) is denied
        $response = $this->actingAs($anotherUser)->get(route('bookings.show', $booking));
        $response->assertStatus(403);

        // Alice cannot cancel John's booking
        $response = $this->actingAs($anotherUser)->post(route('bookings.cancel', $booking));
        $response->assertStatus(403);
    }
}
