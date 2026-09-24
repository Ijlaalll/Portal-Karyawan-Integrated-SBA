<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Notification;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Exception;

class MeetingRoomRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $ga;
    protected User $it;
    protected Room $room;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $this->user->modules()->create(['module' => 'meeting_room']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->ga = User::create([
            'name' => 'GA Staff',
            'email' => 'ga@example.com',
            'password' => Hash::make('password'),
            'role' => 'ga',
        ]);
        $this->ga->modules()->create(['module' => 'meeting_room']);

        $this->it = User::create([
            'name' => 'IT Staff',
            'email' => 'it@example.com',
            'password' => Hash::make('password'),
            'role' => 'it',
        ]);
        $this->it->modules()->create(['module' => 'meeting_room']);

        $this->room = Room::create([
            'name' => 'Innovation Room',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->bookingService = app(BookingService::class);
    }

    /**
     * Test offline booking with room capacity constraint.
     */
    public function test_offline_booking_enforces_capacity_limit(): void
    {
        // 1. Participant count matches capacity -> Success
        $dataMatch = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Offline Board Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'participant_count' => 10,
        ];
        $booking = $this->bookingService->createBooking($this->user, $dataMatch);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'meeting_type' => 'offline',
            'participant_count' => 10,
        ]);

        // 2. Participant count exceeds capacity -> Exception
        $dataExceed = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Overcrowded Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'participant_count' => 11, // Room capacity is 10
        ];

        $this->expectException(Exception::class);
        $this->bookingService->createBooking($this->user, $dataExceed);
    }

    /**
     * Test 30-minute minimum duration rule.
     */
    public function test_booking_minimum_duration_is_30_minutes(): void
    {
        // Exactly 30 minutes -> Success
        $data30Min = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Quick Daily Standup',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'participant_count' => 5,
        ];
        $booking = $this->bookingService->createBooking($this->user, $data30Min);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);

        // Less than 30 minutes (e.g. 20 minutes) -> Exception
        $data20Min = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Too Short Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '09:20',
            'participant_count' => 5,
        ];

        $this->expectException(Exception::class);
        $this->bookingService->createBooking($this->user, $data20Min);
    }

    /**
     * Test that booking creation automatically triggers approved status and appropriate staff notifications.
     */
    public function test_booking_creation_triggers_auto_approval_and_notifications(): void
    {
        // 1. Create Offline Booking -> Approved + Notifies GA only
        $offlineData = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Weekly Offline Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'participant_count' => 4,
        ];

        $offlineBooking = $this->bookingService->createBooking($this->user, $offlineData);

        $this->assertEquals('approved', $offlineBooking->status);
        $this->assertEquals('pending', $offlineBooking->ga_status);
        $this->assertNull($offlineBooking->it_status);

        // GA gets notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ga->id,
            'title' => 'Tugas Persiapan Ruangan Baru',
        ]);

        // IT does NOT get notification
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->it->id,
            'title' => 'Tugas Persiapan Ruangan Baru',
        ]);

        // 2. Create Hybrid Booking -> Approved + Notifies GA and IT
        $hybridData = [
            'meeting_type' => 'hybrid',
            'room_id' => $this->room->id,
            'title' => 'Weekly Hybrid Sync',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'participant_count' => 4,
        ];

        $hybridBooking = $this->bookingService->createBooking($this->user, $hybridData);

        $this->assertEquals('approved', $hybridBooking->status);
        $this->assertEquals('pending', $hybridBooking->ga_status);
        $this->assertEquals('pending', $hybridBooking->it_status);

        // GA gets notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ga->id,
            'title' => 'Tugas Persiapan Ruangan Baru',
        ]);

        // IT gets notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->it->id,
            'title' => 'Tugas Persiapan Meeting Hybrid Baru',
        ]);
    }


    /**
     * Test GA can mark offline booking as ready and notifies user.
     */
    public function test_ga_can_mark_offline_booking_as_ready_and_notifies_user(): void
    {
        $offlineBooking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline Lunch Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '12:00',
            'end_time' => '13:00',
            'meeting_type' => 'offline',
            'participant_count' => 10,
            'status' => 'approved',
            'preparation_status' => 'Menunggu Persiapan',
        ]);

        $this->actingAs($this->ga);

        $response = $this->post(route('preparations.status.update', $offlineBooking), [
            'preparation_status' => 'Siap',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $offlineBooking->id,
            'preparation_status' => 'Siap',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Ruangan Siap Digunakan',
        ]);
    }

    /**
     * Test booking cancellation invalidates active preparation tasks.
     */
    public function test_booking_cancellation_invalidates_preparation_tasks(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline Board Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'participant_count' => 5,
            'status' => 'approved',
            'preparation_status' => 'Menunggu Persiapan',
        ]);

        // Act as GA - task is in active tasks
        $this->actingAs($this->ga);
        $responseGA1 = $this->get(route('ga.preparations'));
        $responseGA1->assertSee('Offline Board Meeting');

        // Act as Admin/User to cancel
        $this->actingAs($this->user);
        $this->post(route('bookings.cancel', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        // Act as GA - task is no longer in active tasks
        $this->actingAs($this->ga);
        $responseGA2 = $this->get(route('ga.preparations'));
        $responseGA2->assertDontSee('Offline Board Meeting');
    }


    /**
     * Test ready notification popup acknowledgement logic.
     */
    public function test_ready_notification_popup_acknowledgement(): void
    {
        $offlineBooking = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Ready Offline Event',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '15:00',
            'end_time' => '16:00',
            'meeting_type' => 'offline',
            'participant_count' => 10,
            'status' => 'approved',
            'preparation_status' => 'Siap',
        ]);

        $offlineBooking->refresh();
        $this->actingAs($this->user);

        // Initially ready booking is false
        $this->assertFalse($offlineBooking->ready_popup_acknowledged);

        // Acknowledge via POST request
        $response = $this->post(route('bookings.acknowledge-ready', $offlineBooking));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Database now has ready_popup_acknowledged as true
        $offlineBooking->refresh();
        $this->assertTrue($offlineBooking->ready_popup_acknowledged);
    }


    /**
     * Test GA, IT, Hybrid Meeting and Booking Flow revisions.
     */
    public function test_meeting_room_reservation_system_revision_requirements(): void
    {
        // 1. Bypass Admin Approval & Task Assignment (Offline)
        $offlineData = [
            'meeting_type' => 'offline',
            'room_id' => $this->room->id,
            'title' => 'Offline Board Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'participant_count' => 5,
        ];
        
        $bookingOffline = $this->bookingService->createBooking($this->user, $offlineData);
        
        // Assert offline booking is approved immediately and only GA task is created
        $this->assertEquals('approved', $bookingOffline->status);
        $this->assertEquals('pending', $bookingOffline->ga_status);
        $this->assertNull($bookingOffline->it_status);

        // 2. Bypass Admin Approval & Task Assignment (Hybrid)
        $hybridData = [
            'meeting_type' => 'hybrid',
            'room_id' => $this->room->id,
            'title' => 'Hybrid Alignment Meeting',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '11:30',
            'end_time' => '12:30',
            'participant_count' => 6,
        ];
        
        $bookingHybrid = $this->bookingService->createBooking($this->user, $hybridData);
        
        // Assert hybrid booking is approved immediately and both GA/IT tasks are created
        $this->assertEquals('approved', $bookingHybrid->status);
        $this->assertEquals('pending', $bookingHybrid->ga_status);
        $this->assertEquals('pending', $bookingHybrid->it_status);

        // 3. Worker Tracking and Status Updates
        $this->actingAs($this->ga);
        $response = $this->post(route('preparations.status.update', $bookingHybrid), [
            'preparation_status' => 'in_progress',
        ]);
        $response->assertRedirect();
        $bookingHybrid->refresh();
        $this->assertEquals('in_progress', $bookingHybrid->ga_status);
        $this->assertEquals($this->ga->id, $bookingHybrid->prepared_by_ga);

        $this->actingAs($this->it);
        $response = $this->post(route('preparations.status.update', $bookingHybrid), [
            'preparation_status' => 'ready',
        ]);
        $response->assertRedirect();
        $bookingHybrid->refresh();
        $this->assertEquals('ready', $bookingHybrid->it_status);
        $this->assertEquals($this->it->id, $bookingHybrid->prepared_by_it);

        // 4. Popup Sequential and Independent Display
        // Since ga_status = in_progress (not ready), but it_status = ready:
        // IT Ready Popup should display because it is independent!
        $this->actingAs($this->user);
        $response = $this->get(route('bookings.index')); // layout app popups will load
        $response->assertSee('Fasilitas Hybrid Siap!');
        $response->assertDontSee('Ruangan Siap Digunakan!');

        // Acknowledge IT popup
        $response = $this->post(route('bookings.acknowledge-active', $bookingHybrid));
        $response->assertJson(['success' => true]);
        $bookingHybrid->refresh();
        $this->assertTrue($bookingHybrid->it_ready_popup_acknowledged);

        // Now update GA task to ready
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bookingHybrid), [
            'preparation_status' => 'ready',
        ]);
        
        // GA Ready Popup should now display
        $this->actingAs($this->user);
        $response = $this->get(route('bookings.index'));
        $response->assertSee('Ruangan Siap Digunakan!');

        // Acknowledge GA popup
        $response = $this->post(route('bookings.acknowledge-ready', $bookingHybrid));
        $response->assertJson(['success' => true]);
        $bookingHybrid->refresh();
        $this->assertTrue($bookingHybrid->ga_ready_popup_acknowledged);

        // 5. Worker statistics on Admin User details show view
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.users.show', $this->ga));
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Hasil Kerja Persiapan (GA)');
        
        // 6. User Deactivation/Delete Rules
        // Deleting GA worker with task history should be blocked!
        $response = $this->delete(route('admin.users.destroy', $this->ga));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun GA/IT tidak dapat dihapus karena memiliki riwayat tugas persiapan.');
        $this->assertDatabaseHas('users', ['id' => $this->ga->id]); // GA worker is preserved
    }

    /**
     * Test comprehensive auto-cancellation, timeout, popup alerts, and room availability workflows.
     */
    public function test_booking_auto_cancellation_and_popup_workflows(): void
    {
        $availabilityService = app(\App\Services\AvailabilityService::class);

        // --- IMMEDIATE CANCELLATIONS (TRIGGER A) ---

        // 1. Offline + GA not_ready -> cancelled
        $bOfflineImmediate = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline immediate failure',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'approved',
            'ga_status' => 'pending',
        ]);
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bOfflineImmediate), ['preparation_status' => 'not_ready', 'cancellation_note' => 'Ruangan tidak siap.']);
        $bOfflineImmediate->refresh();
        $this->assertEquals('cancelled', $bOfflineImmediate->status);
        $this->assertEquals('GA_FAILURE', $bOfflineImmediate->cancellation_reason);
        $this->assertFalse($bOfflineImmediate->cancellation_popup_acknowledged);

        // Assert Immediate Cancellation Popup displays the correct message (GA Failure)
        $this->actingAs($this->user);
        $response = $this->get(route('bookings.index'));
        $response->assertSee('Meeting Dibatalkan');
        $response->assertSee('Persiapan ruangan oleh Tim GA tidak siap.');

        // Acknowledge the immediate popup
        $this->post(route('bookings.acknowledge-cancellation', $bOfflineImmediate));
        $response = $this->get(route('bookings.index'));
        $response->assertDontSee('Persiapan ruangan oleh Tim GA tidak siap.');

        // 2. Online + IT not_ready -> cancelled
        $bOnlineImmediate = Booking::create([
            'user_id' => $this->user->id,
            'title' => 'Online immediate failure',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'online',
            'status' => 'approved',
            'preparation_status' => 'Menunggu Persiapan',
        ]);
        $this->actingAs($this->it);
        $this->post(route('preparations.status.update', $bOnlineImmediate), ['preparation_status' => 'not_ready', 'cancellation_note' => 'Koneksi teknis gagal.']);
        $bOnlineImmediate->refresh();
        $this->assertEquals('cancelled', $bOnlineImmediate->status);
        $this->assertEquals('IT_FAILURE', $bOnlineImmediate->cancellation_reason);
        $this->assertFalse($bOnlineImmediate->cancellation_popup_acknowledged);

        // Acknowledge this popup to keep stack clean
        $this->actingAs($this->user);
        $this->post(route('bookings.acknowledge-cancellation', $bOnlineImmediate));

        // 3. Hybrid + GA not_ready -> cancelled
        $bHybridImmediateGA = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid GA immediate failure',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'pending',
            'it_status' => 'pending',
        ]);
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bHybridImmediateGA), ['preparation_status' => 'not_ready', 'cancellation_note' => 'Ruangan tidak siap.']);
        $bHybridImmediateGA->refresh();
        $this->assertEquals('cancelled', $bHybridImmediateGA->status);
        $this->assertEquals('GA_FAILURE', $bHybridImmediateGA->cancellation_reason);
        
        $this->actingAs($this->user);
        $this->post(route('bookings.acknowledge-cancellation', $bHybridImmediateGA));

        // 4. Hybrid + IT not_ready -> cancelled
        $bHybridImmediateIT = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid IT immediate failure',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'pending',
            'it_status' => 'pending',
        ]);
        $this->actingAs($this->it);
        $this->post(route('preparations.status.update', $bHybridImmediateIT), ['preparation_status' => 'not_ready', 'cancellation_note' => 'Peralatan teknis tidak tersedia.']);
        $bHybridImmediateIT->refresh();
        $this->assertEquals('cancelled', $bHybridImmediateIT->status);
        $this->assertEquals('IT_FAILURE', $bHybridImmediateIT->cancellation_reason);

        $this->actingAs($this->user);
        $this->post(route('bookings.acknowledge-cancellation', $bHybridImmediateIT));

        // 5. Hybrid + both not_ready -> cancelled
        $bHybridImmediateBoth = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid both immediate failure',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'ga_status' => 'not_ready',
            'it_status' => 'pending',
            'cancellation_reason' => 'GA_FAILURE',
            'status' => 'approved',
        ]);
        $this->actingAs($this->it);
        $this->post(route('preparations.status.update', $bHybridImmediateBoth), ['preparation_status' => 'not_ready', 'cancellation_note' => 'IT: Peralatan teknis tidak tersedia.']);
        $bHybridImmediateBoth->refresh();
        $this->assertEquals('cancelled', $bHybridImmediateBoth->status);
        $this->assertEquals('BOTH_FAILURE', $bHybridImmediateBoth->cancellation_reason);

        $this->actingAs($this->user);
        $this->post(route('bookings.acknowledge-cancellation', $bHybridImmediateBoth));


        // --- TIMEOUT CANCELLATIONS (TRIGGER B) ---
        $yesterday = now()->subDay()->format('Y-m-d');

        // 6. Offline + GA pending sampai end_time -> cancelled
        $bOfflineTimeoutPending = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline timeout pending',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'approved',
            'ga_status' => 'pending',
        ]);

        // 7. Offline + GA preparing sampai end_time -> cancelled
        $bOfflineTimeoutPreparing = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline timeout preparing',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'approved',
            'ga_status' => 'in_progress',
        ]);

        // 8. Online + IT pending sampai end_time -> cancelled
        $bOnlineTimeoutPending = Booking::create([
            'user_id' => $this->user->id,
            'title' => 'Online timeout pending',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'online',
            'status' => 'approved',
            'preparation_status' => 'Menunggu Persiapan',
        ]);

        // 9. Online + IT preparing sampai end_time -> cancelled
        $bOnlineTimeoutPreparing = Booking::create([
            'user_id' => $this->user->id,
            'title' => 'Online timeout preparing',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'online',
            'status' => 'approved',
            'preparation_status' => 'Sedang Dipersiapkan',
        ]);

        // 10. Hybrid + GA belum ready sampai end_time -> cancelled
        $bHybridTimeoutGA = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid timeout GA',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'pending',
            'it_status' => 'ready',
        ]);

        // 11. Hybrid + IT belum ready sampai end_time -> cancelled
        $bHybridTimeoutIT = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid timeout IT',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'ready',
            'it_status' => 'pending',
        ]);

        // 12. Hybrid + GA dan IT belum ready sampai end_time -> cancelled
        $bHybridTimeoutBoth = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid timeout both',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'pending',
            'it_status' => 'in_progress',
        ]);

        // --- POSITIVE CASES (NOT CANCELLED, TRANSITION TO COMPLETED) ---

        // 13. Offline + GA ready sebelum end_time -> tidak cancelled
        $bOfflineSuccess = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Offline ready success',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'approved',
            'ga_status' => 'ready',
        ]);

        // 14. Online + IT ready sebelum end_time -> tidak cancelled
        $bOnlineSuccess = Booking::create([
            'user_id' => $this->user->id,
            'title' => 'Online ready success',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'online',
            'status' => 'approved',
            'preparation_status' => 'Siap',
        ]);

        // 15. Hybrid + GA dan IT ready sebelum end_time -> tidak cancelled
        $bHybridSuccess = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Hybrid ready success',
            'booking_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'hybrid',
            'status' => 'approved',
            'ga_status' => 'ready',
            'it_status' => 'ready',
        ]);

        // Run the background timeout syncing command
        Booking::syncCompletedBookings();

        // Assert Timeout Cancellations
        $bOfflineTimeoutPending->refresh();
        $this->assertEquals('cancelled', $bOfflineTimeoutPending->status);
        $this->assertEquals('GA_TIMEOUT', $bOfflineTimeoutPending->cancellation_reason);

        $bOfflineTimeoutPreparing->refresh();
        $this->assertEquals('cancelled', $bOfflineTimeoutPreparing->status);
        $this->assertEquals('GA_TIMEOUT', $bOfflineTimeoutPreparing->cancellation_reason);

        $bOnlineTimeoutPending->refresh();
        $this->assertEquals('cancelled', $bOnlineTimeoutPending->status);
        $this->assertEquals('IT_TIMEOUT', $bOnlineTimeoutPending->cancellation_reason);

        $bOnlineTimeoutPreparing->refresh();
        $this->assertEquals('cancelled', $bOnlineTimeoutPreparing->status);
        $this->assertEquals('IT_TIMEOUT', $bOnlineTimeoutPreparing->cancellation_reason);

        $bHybridTimeoutGA->refresh();
        $this->assertEquals('cancelled', $bHybridTimeoutGA->status);
        $this->assertEquals('GA_TIMEOUT', $bHybridTimeoutGA->cancellation_reason);

        $bHybridTimeoutIT->refresh();
        $this->assertEquals('cancelled', $bHybridTimeoutIT->status);
        $this->assertEquals('IT_TIMEOUT', $bHybridTimeoutIT->cancellation_reason);

        $bHybridTimeoutBoth->refresh();
        $this->assertEquals('cancelled', $bHybridTimeoutBoth->status);
        $this->assertEquals('BOTH_TIMEOUT', $bHybridTimeoutBoth->cancellation_reason);

        // Assert Success/Completed Transitions
        $bOfflineSuccess->refresh();
        $this->assertEquals('completed', $bOfflineSuccess->status);

        $bOnlineSuccess->refresh();
        $this->assertEquals('completed', $bOnlineSuccess->status);

        $bHybridSuccess->refresh();
        $this->assertEquals('completed', $bHybridSuccess->status);


        // --- IMMUTABLE CANCELLATION (CASE F) ---

        // 16. cancelled -> preparation berubah menjadi ready -> tetap cancelled
        $bCancelledImmutable = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Immutable check',
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'cancelled',
            'ga_status' => 'not_ready',
        ]);
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bCancelledImmutable), ['preparation_status' => 'ready']);
        $bCancelledImmutable->refresh();
        $this->assertEquals('cancelled', $bCancelledImmutable->status);
        $this->assertEquals('ready', $bCancelledImmutable->ga_status);


        // --- POPUPS & DISMISSALS ---

        // 17. Cancellation Popup muncul setelah timeout
        $this->actingAs($this->user);
        $response = $this->get(route('bookings.index'));
        $response->assertSee('Meeting Dibatalkan');

        // Acknowledge all pending timeout popups to verify clean dismissal
        $pendingTimeoutCancellations = Booking::where('user_id', $this->user->id)
            ->where('status', 'cancelled')
            ->where('cancellation_popup_acknowledged', false)
            ->get();
            
        foreach ($pendingTimeoutCancellations as $b) {
            $this->post(route('bookings.acknowledge-cancellation', $b));
        }

        // Assert no more cancellation modal is shown
        $response = $this->get(route('bookings.index'));
        $response->assertDontSee('Meeting Dibatalkan');

        // 21. Cancelled booking tidak memunculkan Ready Popup
        $bCancelledNoReady = Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'title' => 'Cancelled popup ready test',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'meeting_type' => 'offline',
            'status' => 'cancelled',
            'ga_status' => 'ready',
            'ga_ready_popup_acknowledged' => false,
        ]);
        $response = $this->get(route('bookings.index'));
        $response->assertDontSee('Ruangan Siap Digunakan!');

        // 22. Cancelled booking tidak memunculkan Active Meeting Popup
        $response->assertDontSee('Waktunya Meeting Online!');



        // 24. Cancelled booking tidak memblokir room slot
        $this->assertTrue($availabilityService->isRoomAvailable(
            $this->room->id,
            now()->addDay()->format('Y-m-d'),
            '10:00',
            '11:00',
            $bOfflineImmediate->id // Exclude self
        ));
    }

    /**
     * Test user cancellation locks, cancellation metadata, and preparation-lock enforcement.
     */
    public function test_user_cancellation_lock_and_metadata(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // 1. Offline booking with GA pending → user can cancel
        $bOfflinePending = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'Offline Cancel Pending',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'offline',
            'status'       => 'approved',
            'ga_status'    => 'pending',
        ]);
        $this->actingAs($this->user);
        $response = $this->post(route('bookings.cancel', $bOfflinePending));
        $bOfflinePending->refresh();
        $this->assertEquals('cancelled', $bOfflinePending->status);

        // 2. Cancelled by user → cancellation_reason=USER_CANCEL, cancelled_by=user, popup_acknowledged=true
        $this->assertEquals('USER_CANCEL', $bOfflinePending->cancellation_reason);
        $this->assertEquals($this->user->id, $bOfflinePending->cancelled_by);
        $this->assertNotNull($bOfflinePending->cancelled_at);
        $this->assertTrue($bOfflinePending->cancellation_popup_acknowledged);

        // 3. Offline booking with GA in_progress → user CANNOT cancel (locked)
        $bOfflineLocked = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'Offline Locked Preparing',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'offline',
            'status'       => 'approved',
            'ga_status'    => 'in_progress',
        ]);
        $response = $this->post(route('bookings.cancel', $bOfflineLocked));
        $response->assertStatus(403);
        $bOfflineLocked->refresh();
        $this->assertEquals('approved', $bOfflineLocked->status);

        // 4. Online booking with IT in_progress → user CANNOT cancel
        $bOnlineLocked = Booking::create([
            'user_id'      => $this->user->id,
            'title'        => 'Online Locked Preparing',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'online',
            'status'       => 'approved',
            'it_status'    => 'in_progress',
        ]);
        $response = $this->post(route('bookings.cancel', $bOnlineLocked));
        $response->assertStatus(403);
        $bOnlineLocked->refresh();
        $this->assertEquals('approved', $bOnlineLocked->status);

        // 5. Hybrid with GA in_progress and IT pending → user CANNOT cancel
        $bHybridLockedGA = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'Hybrid Locked GA',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'hybrid',
            'status'       => 'approved',
            'ga_status'    => 'in_progress',
            'it_status'    => 'pending',
        ]);
        $response = $this->post(route('bookings.cancel', $bHybridLockedGA));
        $response->assertStatus(403);
        $bHybridLockedGA->refresh();
        $this->assertEquals('approved', $bHybridLockedGA->status);

        // 6. Hybrid with GA pending and IT in_progress → user CANNOT cancel
        $bHybridLockedIT = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'Hybrid Locked IT',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'hybrid',
            'status'       => 'approved',
            'ga_status'    => 'pending',
            'it_status'    => 'in_progress',
        ]);
        $response = $this->post(route('bookings.cancel', $bHybridLockedIT));
        $response->assertStatus(403);
        $bHybridLockedIT->refresh();
        $this->assertEquals('approved', $bHybridLockedIT->status);

        // 7. GA not_ready with note → cancellation_note and cancelled_by stored
        $bGANotReady = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'GA Note Metadata',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'offline',
            'status'       => 'approved',
            'ga_status'    => 'pending',
        ]);
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bGANotReady), [
            'preparation_status' => 'not_ready',
            'cancellation_note'  => 'Ruangan mengalami kendala fasilitas.',
        ]);
        $bGANotReady->refresh();
        $this->assertEquals('cancelled', $bGANotReady->status);
        $this->assertEquals('GA_FAILURE', $bGANotReady->cancellation_reason);
        $this->assertEquals('Ruangan mengalami kendala fasilitas.', $bGANotReady->cancellation_note);
        $this->assertEquals($this->ga->id, $bGANotReady->cancelled_by);
        $this->assertNotNull($bGANotReady->cancelled_at);
        $this->assertFalse($bGANotReady->cancellation_popup_acknowledged);

        // 8. IT not_ready without note → validation error (note required)
        $bITNoNote = Booking::create([
            'user_id'      => $this->user->id,
            'title'        => 'IT No Note',
            'booking_date' => $tomorrow,
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'online',
            'status'       => 'approved',
            'it_status'    => 'pending',
        ]);
        $this->actingAs($this->it);
        $response = $this->post(route('preparations.status.update', $bITNoNote), [
            'preparation_status' => 'not_ready',
            // Missing cancellation_note
        ]);
        $response->assertSessionHasErrors(['cancellation_note']);
        $bITNoNote->refresh();
        $this->assertEquals('approved', $bITNoNote->status); // still approved

        // 9. Timeout cancellations have null cancelled_by and populated note
        $bTimeout = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'Timeout Meta',
            'booking_date' => now()->subDay()->format('Y-m-d'),
            'start_time'   => '10:00',
            'end_time'     => '11:00',
            'meeting_type' => 'offline',
            'status'       => 'approved',
            'ga_status'    => 'pending',
        ]);
        Booking::syncCompletedBookings();
        $bTimeout->refresh();
        $this->assertEquals('cancelled', $bTimeout->status);
        $this->assertEquals('GA_TIMEOUT', $bTimeout->cancellation_reason);
        $this->assertNull($bTimeout->cancelled_by);           // automated — no individual user
        $this->assertNotNull($bTimeout->cancellation_note);   // auto-generated note
        $this->assertNotNull($bTimeout->cancelled_at);
        $this->assertFalse($bTimeout->cancellation_popup_acknowledged);
    }

    public function test_final_cleanups_and_riwayat_tugas()
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Create a booking for GA not_ready
        $bGA = Booking::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'title'        => 'GA Not Ready Booking',
            'booking_date' => $tomorrow,
            'start_time'   => '14:00',
            'end_time'     => '15:00',
            'meeting_type' => 'offline',
            'status'       => 'approved',
            'ga_status'    => 'pending',
        ]);

        // Submit not_ready for GA
        $this->actingAs($this->ga);
        $this->post(route('preparations.status.update', $bGA), [
            'preparation_status' => 'not_ready',
            'cancellation_note'  => 'GA Gagal Siapkan Ruangan.',
        ]);

        // Assert cancelled
        $bGA->refresh();
        $this->assertEquals('cancelled', $bGA->status);
        $this->assertEquals('not_ready', $bGA->ga_status);

        // Assert GA history page includes this booking
        $this->actingAs($this->ga);
        $response = $this->get(route('ga.preparations', ['status' => 'history']));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Tugas');
        $response->assertSee('Tidak Siap');
        $response->assertSee('GA Gagal Siapkan Ruangan.');

        // Assert GA stats include this booking under not_ready and total
        $viewStats = $response->viewData('stats');
        $this->assertEquals(1, $viewStats['not_ready']);
        $this->assertEquals(1, $viewStats['total']);

        // Create a booking for IT not_ready
        $bIT = Booking::create([
            'user_id'      => $this->user->id,
            'title'        => 'IT Not Ready Booking',
            'booking_date' => $tomorrow,
            'start_time'   => '16:00',
            'end_time'     => '17:00',
            'meeting_type' => 'hybrid',
            'status'       => 'approved',
            'it_status'    => 'pending',
        ]);

        // Submit not_ready for IT
        $this->actingAs($this->it);
        $this->post(route('preparations.status.update', $bIT), [
            'preparation_status' => 'not_ready',
            'cancellation_note'  => 'IT Gagal Siapkan Jaringan.',
        ]);

        // Assert cancelled
        $bIT->refresh();
        $this->assertEquals('cancelled', $bIT->status);
        $this->assertEquals('not_ready', $bIT->it_status);

        // Assert IT history page includes this booking
        $this->actingAs($this->it);
        $response = $this->get(route('it.preparations', ['status' => 'history']));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Tugas');
        $response->assertSee('Tidak Siap');
        $response->assertSee('IT Gagal Siapkan Jaringan.');

        // Assert IT stats include this booking under not_ready and total
        $viewStatsIT = $response->viewData('stats');
        $this->assertEquals(1, $viewStatsIT['not_ready']);
        $this->assertEquals(1, $viewStatsIT['total']);

        // Assert admin detail shows the tasks correctly
        $this->actingAs($this->admin);
        $adminResp = $this->get(route('admin.users.show', $this->ga));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('GA Gagal Siapkan Ruangan.');
        
        $adminRespIT = $this->get(route('admin.users.show', $this->it));
        $adminRespIT->assertStatus(200);
        $adminRespIT->assertSee('IT Gagal Siapkan Jaringan.');

        // Assert user details show cancellation info but NO Microsoft Teams link or placeholders
        $this->actingAs($this->user);
        $userResp = $this->get(route('bookings.show', $bGA));
        $userResp->assertStatus(200);
        $userResp->assertSee('Informasi Detail Pembatalan');
        $userResp->assertSee('GA Gagal Siapkan Ruangan.');
        $userResp->assertDontSee('Link Meeting Online');
        $userResp->assertDontSee('Menunggu link meeting diunggah oleh IT');
        $userResp->assertDontSee('Gabung Meeting');

        // Assert dashboard doesn't have "Lihat Kalender" or "Menunggu" or "Ditolak"
        $dashResp = $this->get(route('dashboard'));
        $dashResp->assertStatus(200);
        $dashResp->assertDontSee('Lihat Kalender');
        $dashResp->assertDontSee('Menunggu');
        $dashResp->assertDontSee('Ditolak');
    }
}
