<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class BookingReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected User $admin;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->room = Room::create([
            'name' => 'Innovation Room',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);
    }

    /**
     * Test review form submission rules.
     */
    public function test_user_can_submit_review_for_own_completed_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Important Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.review.store', $booking), [
            'rating' => 5,
            'feedback' => 'Very clean and spacious room!',
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'rating' => 5,
            'feedback' => 'Very clean and spacious room!',
        ]);
    }

    public function test_user_cannot_submit_review_for_other_users_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Important Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user2)->post(route('bookings.review.store', $booking), [
            'rating' => 4,
            'feedback' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
        $this->assertNull($booking->fresh()->rating);
    }

    public function test_user_cannot_submit_review_for_non_completed_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Upcoming Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.review.store', $booking), [
            'rating' => 5,
            'feedback' => 'Should fail',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertNull($booking->fresh()->rating);
    }

    public function test_rating_displays_correctly_on_dashboards(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed and Rated Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'rating' => 5,
            'feedback' => 'Top notch service.',
        ]);

        // Access Global Admin Dashboard
        $responseGlobal = $this->actingAs($this->admin)->get(route('admin.home'));
        $responseGlobal->assertStatus(200);
        $responseGlobal->assertSee('Rating Rata-rata');
        $responseGlobal->assertSee('★ 5');

        // Access Meeting Room Admin Dashboard
        $responseDetailed = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $responseDetailed->assertStatus(200);
        $responseDetailed->assertSee('★ 5');
        $responseDetailed->assertSee('Top notch service.');
    }

    public function test_admin_can_access_full_booking_history_page(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Important Board Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.booking_history', ['search' => 'Important Board']));
        $response->assertStatus(200);
        $response->assertSee('Important Board Meeting');
        $response->assertSee('Riwayat Penggunaan Ruangan');
    }

    public function test_admin_can_access_full_reviews_page(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Important Rated Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'rating' => 4,
            'feedback' => 'Nice acoustics in the room',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reviews', ['rating' => 4]));
        $response->assertStatus(200);
        $response->assertSee('Nice acoustics in the room');
        $response->assertSee('Semua Ulasan Ruangan');
    }

    public function test_user_cannot_cancel_started_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Started Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => now()->subHour()->format('H:i:s'),
            'end_time' => now()->addHour()->format('H:i:s'),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.cancel', $booking));
        $response->assertStatus(403);
        $this->assertEquals('approved', $booking->fresh()->status);
    }

    public function test_user_cannot_cancel_non_pending_approved_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Rejected Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.cancel', $booking));
        $response->assertStatus(403);
        $this->assertEquals('rejected', $booking->fresh()->status);
    }

    public function test_approved_booking_automatically_completed_after_end_time(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Past Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(2)->format('H:i:s'),
            'end_time' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user1)->get(route('bookings.show', $booking));
        $response->assertStatus(200);

        $this->assertEquals('completed', $booking->fresh()->status);
    }

    public function test_pending_booking_not_completed_after_end_time(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Past Pending Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(2)->format('H:i:s'),
            'end_time' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user1)->get(route('bookings.show', $booking));
        $response->assertStatus(200);

        $this->assertEquals('pending', $booking->fresh()->status);
    }

    public function test_review_rating_validation_bounds(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.review.store', $booking), [
            'rating' => 6,
            'feedback' => 'Nice',
        ]);
        $response->assertSessionHasErrors('rating');

        $response = $this->actingAs($this->user1)->post(route('bookings.review.store', $booking), [
            'rating' => 0,
            'feedback' => 'Nice',
        ]);
        $response->assertSessionHasErrors('rating');
    }

    public function test_review_duplicate_submission_blocked(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'rating' => 5,
            'feedback' => 'First review',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.review.store', $booking), [
            'rating' => 4,
            'feedback' => 'Second review attempt',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals(5, $booking->fresh()->rating);
    }

    public function test_review_privacy_rules(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Rated Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'rating' => 5,
            'feedback' => 'Top secret review text',
        ]);

        $response1 = $this->actingAs($this->user1)->get(route('bookings.show', $booking));
        $response1->assertSee('Top secret review text');

        $responseAdmin = $this->actingAs($this->admin)->get(route('bookings.show', $booking));
        $responseAdmin->assertSee('Top secret review text');

        $response2 = $this->actingAs($this->user2)->get(route('bookings.show', $booking));
        $response2->assertDontSee('Top secret review text');
    }

    public function test_user_can_snooze_review(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Meeting To Snooze',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user1)->post(route('bookings.review.snooze', $booking));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertContains($booking->id, session('snoozed_bookings', []));
    }

    public function test_user_sees_pending_review_popup_when_eligible(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Completed Meeting For Popup',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user1)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Bagaimana meeting Anda?');
        $response->assertSee('Innovation Room');
    }

    public function test_normal_user_cannot_access_admin_ratings_page(): void
    {
        $response = $this->actingAs($this->user1)->get(route('admin.reviews'));
        $response->assertStatus(403);
    }

    public function test_admin_ratings_filters_work_together(): void
    {
        $roomB = Room::create([
            'name' => 'Board Room',
            'location' => 'Lantai 2',
            'capacity' => 12,
            'status' => 'active',
        ]);

        $bookingA = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'Meeting Room A',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'rating' => 5,
            'feedback' => 'Loved Innovation Room',
        ]);

        $bookingB = Booking::create([
            'user_id' => $this->user2->id,
            'room_id' => $roomB->id,
            'title' => 'Meeting Room B',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => 'completed',
            'rating' => 4,
            'feedback' => 'Okay Board Room',
        ]);

        // Filter: Room A + 5 Stars + Search "Loved"
        $response = $this->actingAs($this->admin)->get(route('admin.reviews', [
            'room_id' => $this->room->id,
            'rating' => 5,
            'search' => 'Loved',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Loved Innovation Room');
        $response->assertDontSee('Okay Board Room');
    }

    public function test_admin_can_access_room_performance_detail_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.room_performance'));
        $response->assertStatus(200);
        $response->assertSee('Detail Performa Ruangan');
        $response->assertSee('Innovation Room');
    }

    public function test_normal_user_cannot_access_room_performance_detail_page(): void
    {
        $response = $this->actingAs($this->user1)->get(route('admin.room_performance'));
        $response->assertStatus(403);
    }

    public function test_room_usage_history_supports_user_filtering(): void
    {
        // Booking for User 1
        $booking1 = Booking::create([
            'user_id' => $this->user1->id,
            'room_id' => $this->room->id,
            'title' => 'User One Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        // Booking for User 2
        $booking2 = Booking::create([
            'user_id' => $this->user2->id,
            'room_id' => $this->room->id,
            'title' => 'User Two Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => 'completed',
        ]);

        // Request with user_id = User 1
        $response = $this->actingAs($this->admin)->get(route('admin.booking_history', [
            'user_id' => $this->user1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('User One Meeting');
        $response->assertDontSee('User Two Meeting');
    }
}
