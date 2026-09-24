<?php

namespace Tests\Feature;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestHistory;
use App\Models\AtkRequestItem;
use App\Models\AtkVariant;
use App\Models\Booking;
use App\Models\CostCenter;
use App\Models\CostCenterTransaction;
use App\Models\Room;
use App\Models\User;
use App\Services\AtkRequestService;
use App\Services\BookingService;
use App\Services\CostCenterTransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected CostCenter $costCenter;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->costCenter = CostCenter::create([
            'cost_center_code'   => 'CC-101',
            'name'               => 'IT Department',
            'description'        => 'Information Technology Division',
            'person_responsible' => 'Manager IT',
            'company_code'       => 'ID50',
            'status'             => 'active',
        ]);

        $this->admin = User::factory()->create([
            'role'           => 'admin',
            'status'         => 'active',
            'department'     => 'Management',
            'cost_center_id' => $this->costCenter->id,
        ]);

        $this->user = User::factory()->create([
            'role'           => 'user',
            'status'         => 'active',
            'department'     => 'Software Engineering',
            'cost_center_id' => $this->costCenter->id,
        ]);
        $this->user->modules()->create(['module' => 'meeting_room']);
        $this->user->modules()->create(['module' => 'atk']);

        $this->room = Room::create([
            'name'              => 'Ruang Meeting Alpha',
            'location'          => 'Lantai 2',
            'capacity'          => 10,
            'description'       => 'Ruangan VIP',
            'price_per_hour'    => 50000.00,
            'is_pricing_active' => true,
            'status'            => 'active',
        ]);
    }

    public function test_room_booking_creates_cost_center_transaction_with_snapshot_rate(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $booking = app(BookingService::class)->createBooking($this->user, [
            'room_id'           => $this->room->id,
            'title'             => 'Sprint Planning Meeting',
            'booking_date'      => $tomorrow,
            'start_time'        => '09:00:00',
            'end_time'          => '11:00:00',
            'meeting_type'      => 'offline',
            'participant_count' => 5,
        ]);

        $this->assertEquals('approved', $booking->status);

        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id'    => $this->costCenter->id,
            'user_id'           => $this->user->id,
            'activity_type'     => 'ROOM_BOOKING',
            'reference_id'      => $booking->id,
            'reference_item_id' => 0,
            'unit_price'        => 50000.00,
            'quantity'          => 2.00,
            'total_amount'      => 100000.00,
            'status'            => 'posted',
        ]);
    }

    public function test_duplicate_room_booking_workflow_is_idempotent(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $booking = app(BookingService::class)->createBooking($this->user, [
            'room_id'           => $this->room->id,
            'title'             => 'Architecture Review',
            'booking_date'      => $tomorrow,
            'start_time'        => '13:00:00',
            'end_time'          => '15:00:00',
            'meeting_type'      => 'offline',
            'participant_count' => 4,
        ]);

        // Manually invoke service multiple times
        $service = app(CostCenterTransactionService::class);
        $service->recordRoomBooking($booking);
        $service->recordRoomBooking($booking);

        $count = CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
            ->where('reference_id', $booking->id)
            ->where('status', 'posted')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_room_booking_cancellation_creates_single_reversal_transaction(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $booking = app(BookingService::class)->createBooking($this->user, [
            'room_id'           => $this->room->id,
            'title'             => 'Weekly Sync',
            'booking_date'      => $tomorrow,
            'start_time'        => '10:00:00',
            'end_time'          => '11:00:00',
            'meeting_type'      => 'offline',
            'participant_count' => 3,
        ]);

        app(BookingService::class)->cancelBooking($booking, $this->user);

        // Original posted transaction remains
        $this->assertDatabaseHas('cost_center_transactions', [
            'reference_id' => $booking->id,
            'status'       => 'posted',
            'total_amount' => 50000.00,
        ]);

        // Reversal transaction exists
        $this->assertDatabaseHas('cost_center_transactions', [
            'reference_id' => $booking->id,
            'status'       => 'reversed',
            'total_amount' => -50000.00,
        ]);

        // Multiple cancellations do not duplicate reversal
        $service = app(CostCenterTransactionService::class);
        $service->reverseRoomBooking($booking);
        $service->reverseRoomBooking($booking);

        $reversalCount = CostCenterTransaction::where('reference_id', $booking->id)
            ->where('status', 'reversed')
            ->count();

        $this->assertEquals(1, $reversalCount);
    }

    public function test_atk_request_completion_creates_transactions_for_each_item(): void
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis']);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen']);
        
        $variant1 = AtkVariant::create([
            'item_id'        => $item->id,
            'name'           => 'Hitam',
            'sku'            => 'PLP-BLK',
            'unit'           => 'pcs',
            'stock'          => 50,
            'purchase_price' => 5000.00,
            'is_active'      => true,
        ]);

        $variant2 = AtkVariant::create([
            'item_id'        => $item->id,
            'name'           => 'Biru',
            'sku'            => 'PLP-BLU',
            'unit'           => 'pcs',
            'stock'          => 50,
            'purchase_price' => 6000.00,
            'is_active'      => true,
        ]);

        // Create and complete ATK request
        $atkService = app(AtkRequestService::class);
        $request = $atkService->createRequest($this->user->id, [
            ['variant_id' => $variant1->id, 'quantity' => 2],
            ['variant_id' => $variant2->id, 'quantity' => 3],
        ], 'Kebutuhan tim');

        $atkService->approveRequest($request->id, $this->admin->id);
        $atkService->processRequest($request->id, $this->admin->id);
        $completedRequest = $atkService->completeRequest($request->id, $this->admin->id);

        $this->assertEquals('selesai', $completedRequest->status);

        // 2 items should create 2 separate transactions
        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id'    => $this->costCenter->id,
            'user_id'           => $this->user->id,
            'activity_type'     => 'ATK_USAGE',
            'reference_id'      => $completedRequest->id,
            'quantity'          => 2.00,
            'unit_price'        => 5000.00,
            'total_amount'      => 10000.00,
            'status'            => 'posted',
        ]);

        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id'    => $this->costCenter->id,
            'user_id'           => $this->user->id,
            'activity_type'     => 'ATK_USAGE',
            'reference_id'      => $completedRequest->id,
            'quantity'          => 3.00,
            'unit_price'        => 6000.00,
            'total_amount'      => 18000.00,
            'status'            => 'posted',
        ]);

        $txCount = CostCenterTransaction::where('activity_type', 'ATK_USAGE')
            ->where('reference_id', $completedRequest->id)
            ->count();

        $this->assertEquals(2, $txCount);
    }

    public function test_changing_room_rate_does_not_affect_historical_transactions(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $booking = app(BookingService::class)->createBooking($this->user, [
            'room_id'           => $this->room->id,
            'title'             => 'Initial Rate Meeting',
            'booking_date'      => $tomorrow,
            'start_time'        => '08:00:00',
            'end_time'          => '10:00:00',
            'meeting_type'      => 'offline',
            'participant_count' => 2,
        ]);

        // Update room rate
        $this->room->update(['price_per_hour' => 150000.00]);

        $transaction = CostCenterTransaction::where('reference_id', $booking->id)->first();
        $this->assertEquals(50000.00, (float)$transaction->unit_price);
        $this->assertEquals(100000.00, (float)$transaction->total_amount);
    }

    public function test_user_management_can_assign_department_and_active_cost_center(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi.santoso@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'user',
            'modules'               => ['meeting_room'],
            'department'            => 'Finance & Accounting',
            'cost_center_id'        => $this->costCenter->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email'          => 'budi.santoso@example.com',
            'department'     => 'Finance & Accounting',
            'cost_center_id' => $this->costCenter->id,
        ]);
    }

    public function test_inactive_cost_center_cannot_be_assigned_to_new_user(): void
    {
        $inactiveCc = CostCenter::create([
            'cost_center_code'   => 'CC-INACTIVE',
            'name'               => 'Old Dept',
            'description'        => 'Decommissioned',
            'person_responsible' => 'None',
            'company_code'       => 'ID50',
            'status'             => 'inactive',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'name'                  => 'Doni Wijaya',
            'email'                 => 'doni.wijaya@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'user',
            'modules'               => ['meeting_room'],
            'department'            => 'Operations',
            'cost_center_id'        => $inactiveCc->id,
        ]);

        $response->assertSessionHasErrors('cost_center_id');
    }

    public function test_cost_center_management_views_accessible_by_admin(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.settings.cost-centers.overview'))->assertOk();
        $this->get(route('admin.settings.cost-centers.index'))->assertOk();
        $this->get(route('admin.settings.cost-centers.pricing'))->assertOk();
        $this->get(route('admin.settings.cost-centers.transactions'))->assertOk();
        $this->get(route('admin.settings.cost-centers.reports'))->assertOk();
    }

    public function test_non_admin_cannot_access_cost_center_management(): void
    {
        $this->actingAs($this->user);

        $this->get(route('admin.settings.cost-centers.overview'))->assertForbidden();
        $this->get(route('admin.settings.cost-centers.index'))->assertForbidden();
    }
}
