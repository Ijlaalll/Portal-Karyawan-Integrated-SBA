<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class AdminApprovalAndRejectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin1;
    protected User $admin2;
    protected User $user;
    protected Room $room;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::create([
            'name' => 'Admin One',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->admin2 = User::create([
            'name' => 'Admin Two',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->room = Room::create([
            'name' => 'Innovation Room',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->bookingService = app(BookingService::class);
    }

    /**
     * Test Pending -> Approved (Admin).
     */
    public function test_admin_can_approve_pending_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Weekly Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $booking));
        $response->assertRedirect(route('admin.approvals.index'));

        $booking->refresh();
        $this->assertEquals('approved', $booking->status);
        $this->assertEquals($this->admin1->id, $booking->approved_by);
        $this->assertNotNull($booking->approved_at);
    }

    /**
     * Test Pending -> Rejected (Admin).
     */
    public function test_admin_can_reject_pending_booking_with_reason(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Weekly Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.reject', $booking), [
            'rejection_reason' => 'Schedule conflict with board meeting.',
        ]);
        $response->assertRedirect(route('admin.approvals.index'));

        $booking->refresh();
        $this->assertEquals('rejected', $booking->status);
        $this->assertEquals('Schedule conflict with board meeting.', $booking->rejection_reason);
        $this->assertNull($booking->approved_by);
    }

    /**
     * Test Rejection requires reason.
     */
    public function test_rejection_requires_rejection_reason(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Weekly Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.reject', $booking), [
            'rejection_reason' => '',
        ]);
        $response->assertSessionHasErrors('rejection_reason');

        $booking->refresh();
        $this->assertEquals('pending', $booking->status);
    }

    /**
     * Test Non-Admin cannot approve/reject.
     */
    public function test_non_admin_cannot_approve_or_reject_bookings(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Weekly Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        // User attempt approve
        $response = $this->actingAs($this->user)->post(route('admin.approvals.approve', $booking));
        $response->assertStatus(403);

        // User attempt reject
        $response = $this->actingAs($this->user)->post(route('admin.approvals.reject', $booking), [
            'rejection_reason' => 'Reason',
        ]);
        $response->assertStatus(403);

        // Guest attempt approve
        Auth::logout();
        $response = $this->post(route('admin.approvals.approve', $booking));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test Invalid Transitions (Approved -> Approved, etc.).
     */
    public function test_only_pending_bookings_can_be_approved_or_rejected(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // 1. Approved booking cannot be approved again
        $bookingApproved = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Approved Booking',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
            'approved_by' => $this->admin1->id,
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $bookingApproved));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('approved', $bookingApproved->fresh()->status);

        // 2. Rejected booking cannot be approved
        $bookingRejected = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Rejected Booking',
            'booking_date' => $date,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $bookingRejected));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('rejected', $bookingRejected->fresh()->status);

        // 3. Cancelled booking cannot be approved
        $bookingCancelled = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Cancelled Booking',
            'booking_date' => $date,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $bookingCancelled));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('cancelled', $bookingCancelled->fresh()->status);

        // 4. Completed booking cannot be approved
        $bookingCompleted = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Booking',
            'booking_date' => $date,
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $bookingCompleted));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('completed', $bookingCompleted->fresh()->status);
    }

    /**
     * Test Concurrency on overlapping approvals.
     */
    public function test_overlapping_pending_bookings_cannot_both_be_approved(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        // Create booking A (pending, 10:00 - 12:00)
        $bookingA = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Booking A',
            'booking_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'pending',
        ]);

        // Create booking B (pending, 11:00 - 13:00) - Overlaps with A
        $bookingB = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Booking B',
            'booking_date' => $date,
            'start_time' => '11:00:00',
            'end_time' => '13:00:00',
            'status' => 'pending',
        ]);

        // 1. Approve booking A
        $responseA = $this->actingAs($this->admin1)->post(route('admin.approvals.approve', $bookingA));
        $responseA->assertRedirect(route('admin.approvals.index'));
        $this->assertEquals('approved', $bookingA->fresh()->status);

        // 2. Attempt to approve booking B -> Should fail because A is approved and blocks the slot
        $responseB = $this->actingAs($this->admin2)->post(route('admin.approvals.approve', $bookingB));
        $responseB->assertSessionHasErrors('error');
        
        $bookingB->refresh();
        $this->assertEquals('pending', $bookingB->status, 'Booking B should remain pending due to scheduling conflict.');
    }
}
