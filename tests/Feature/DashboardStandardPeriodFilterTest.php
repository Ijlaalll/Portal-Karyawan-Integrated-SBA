<?php

namespace Tests\Feature;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkStockMovement;
use App\Models\AtkVariant;
use App\Models\Driver;
use App\Models\ExitPermit;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use App\Models\VehicleRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStandardPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $ga;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'   => 'Admin User',
            'role'   => 'admin',
            'status' => 'active',
        ]);

        $this->ga = User::factory()->create([
            'name'   => 'GA Officer',
            'role'   => 'ga',
            'status' => 'active',
        ]);
        $this->ga->modules()->create(['module' => 'vehicle']);
        $this->ga->modules()->create(['module' => 'atk']);
        $this->ga->modules()->create(['module' => 'sik']);

        $this->user = User::factory()->create([
            'name'   => 'Regular Employee',
            'role'   => 'user',
            'status' => 'active',
        ]);
        $this->user->modules()->create(['module' => 'vehicle']);
        $this->user->modules()->create(['module' => 'atk']);
        $this->user->modules()->create(['module' => 'sik']);
    }

    /**
     * SIK Dashboard respects standard period filter based on permit_date.
     */
    public function test_sik_dashboard_period_filter(): void
    {
        $targetDate = Carbon::create(2026, 5, 15, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($targetDate);

        // Permit inside target month (2026-05-15)
        ExitPermit::create([
            'sik_number'             => 'SIK-202605-00001',
            'user_id'                => $this->user->id,
            'department'             => 'IT',
            'purpose_type'           => 'dinas',
            'purpose'                => 'Client Visit May',
            'permit_date'            => '2026-05-15',
            'expected_departure_time'=> '09:00',
            'expected_return_time'   => '17:00',
            'status'                 => ExitPermit::STATUS_APPROVED,
        ]);

        // Permit outside target month (2026-04-10)
        ExitPermit::create([
            'sik_number'             => 'SIK-202604-00002',
            'user_id'                => $this->user->id,
            'department'             => 'HR',
            'purpose_type'           => 'pribadi',
            'purpose'                => 'Personal April',
            'permit_date'            => '2026-04-10',
            'expected_departure_time'=> '10:00',
            'expected_return_time'   => '12:00',
            'status'                 => ExitPermit::STATUS_APPROVED,
        ]);

        // Test default monthly (May 2026)
        $response = $this->actingAs($this->admin)->get(route('admin.sik.dashboard', [
            'period' => 'monthly',
            'date'   => '2026-05',
        ]));

        $response->assertOk();
        $response->assertViewHas('totalPermits', 1);
        $response->assertSee('SIK-202605-00001');
        $response->assertDontSee('SIK-202604-00002');

        $dailyResponse = $this->actingAs($this->admin)->get(route('admin.sik.dashboard', [
            'period' => 'daily',
            'date'   => '2026-05-15',
        ]));
        $dailyResponse->assertOk();
        $dailyResponse->assertViewHas('totalPermits', 1);

        // Test daily filter for different day with zero permits
        $emptyDaily = $this->actingAs($this->admin)->get(route('admin.sik.dashboard', [
            'period' => 'daily',
            'date'   => '2026-05-16',
        ]));
        $emptyDaily->assertOk();
        $emptyDaily->assertViewHas('totalPermits', 0);

        Carbon::setTestNow();
    }

    /**
     * ATK Dashboard separates all-time inventory from period-based requests and movements.
     */
    public function test_atk_dashboard_period_filter(): void
    {
        $targetDate = Carbon::create(2026, 6, 10, 12, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($targetDate);

        $category = AtkCategory::create(['name' => 'Alat Tulis']);
        $item = AtkItem::create([
            'category_id' => $category->id,
            'name'        => 'Pulpen Gel',
            'unit'        => 'pcs',
        ]);
        $variant = AtkVariant::create([
            'item_id'       => $item->id,
            'name'          => 'Hitam 0.5',
            'sku'           => 'ATK-PLP-001',
            'unit'          => 'pcs',
            'stock'         => 50,
            'minimum_stock' => 10,
            'is_active'     => true,
        ]);

        // Request in June 2026
        $reqJune = AtkRequest::create([
            'request_number' => 'REQ-ATK-202606-0001',
            'user_id'        => $this->user->id,
            'status'         => 'diajukan',
            'notes'          => 'Permintaan Juni',
        ]);
        $reqJune->created_at = Carbon::create(2026, 6, 5, 10, 0, 0, 'Asia/Jakarta');
        $reqJune->save();

        // Movement in June 2026
        AtkStockMovement::create([
            'variant_id'     => $variant->id,
            'type'           => 'restock',
            'quantity'       => 20,
            'previous_stock' => 30,
            'new_stock'      => 50,
            'actor_id'       => $this->admin->id,
            'created_at'     => Carbon::create(2026, 6, 6, 10, 0, 0, 'Asia/Jakarta'),
        ]);

        // Request in January 2026
        $reqJan = AtkRequest::create([
            'request_number' => 'REQ-ATK-202601-0001',
            'user_id'        => $this->user->id,
            'status'         => 'diajukan',
            'notes'          => 'Permintaan Januari',
        ]);
        $reqJan->created_at = Carbon::create(2026, 1, 10, 10, 0, 0, 'Asia/Jakarta');
        $reqJan->save();

        // Movement in January 2026
        AtkStockMovement::create([
            'variant_id'     => $variant->id,
            'type'           => 'restock',
            'quantity'       => 10,
            'previous_stock' => 20,
            'new_stock'      => 30,
            'actor_id'       => $this->admin->id,
            'created_at'     => Carbon::create(2026, 1, 11, 10, 0, 0, 'Asia/Jakarta'),
        ]);

        $response = $this->actingAs($this->admin)->get(route('atk.dashboard', [
            'period' => 'monthly',
            'date'   => '2026-06',
        ]));

        $response->assertOk();
        // Master inventory is all-time
        $response->assertViewHas('totalItems', 1);
        $response->assertViewHas('totalVariants', 1);
        // Requests & movements are period-filtered
        $response->assertViewHas('pendingCount', 1);
        $response->assertViewHas('totalRequestsInPeriod', 1);
        $response->assertSee('REQ-ATK-202606-0001');
        $response->assertDontSee('REQ-ATK-202601-0001');

        Carbon::setTestNow();
    }

    /**
     * Vehicle Dashboard authorization: GA and Admin allowed; regular user redirected.
     */
    public function test_vehicle_dashboard_authorization(): void
    {
        // Admin access
        $adminRes = $this->actingAs($this->admin)->get(route('vehicles.dashboard'));
        $adminRes->assertOk();

        // GA access
        $gaRes = $this->actingAs($this->ga)->get(route('vehicles.dashboard'));
        $gaRes->assertOk();

        // Regular user redirected to requests index
        $userRes = $this->actingAs($this->user)->get(route('vehicles.dashboard'));
        $userRes->assertRedirect(route('vehicles.requests.index'));
    }

    /**
     * Vehicle Dashboard strictly differentiates "Sedang Digunakan Saat Ini" from period-approved requests.
     */
    public function test_vehicle_dashboard_strict_in_use_vs_period_approved_separation(): void
    {
        $now = Carbon::create(2026, 7, 15, 14, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $vehicle1 = Vehicle::create([
            'plate_number' => 'B 1001 SBA',
            'name'         => 'Toyota Avanza',
            'status'       => 'available',
        ]);
        $vehicle2 = Vehicle::create([
            'plate_number' => 'B 1002 SBA',
            'name'         => 'Innova Reborn',
            'status'       => 'available',
        ]);

        $driver1 = Driver::create([
            'name'         => 'Supir Satu',
            'phone_number' => '08123456781',
            'status'       => 'available',
        ]);
        $driver2 = Driver::create([
            'name'         => 'Supir Dua',
            'phone_number' => '08123456782',
            'status'       => 'available',
        ]);

        // Trip 1: Currently In Use Right Now (13:00 to 16:00, now is 14:00)
        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202607-00001',
            'user_id'        => $this->user->id,
            'vehicle_id'     => $vehicle1->id,
            'driver_id'      => $driver1->id,
            'purpose'        => 'Kunjungan Lapangan Sekarang',
            'destination'    => 'Pabrik Karawang',
            'start_datetime' => '2026-07-15 13:00:00',
            'end_datetime'   => '2026-07-15 16:00:00',
            'status'         => 'approved',
            'approved_by'    => $this->ga->id,
        ]);

        // Trip 2: Approved in this month, but in the FUTURE (2026-07-20 09:00 to 12:00)
        // NOT currently in use!
        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202607-00002',
            'user_id'        => $this->user->id,
            'vehicle_id'     => $vehicle2->id,
            'driver_id'      => $driver2->id,
            'purpose'        => 'Kunjungan Minggu Depan',
            'destination'    => 'Kantor Cabang',
            'start_datetime' => '2026-07-20 09:00:00',
            'end_datetime'   => '2026-07-20 12:00:00',
            'status'         => 'approved',
            'approved_by'    => $this->ga->id,
        ]);

        // Fuel Log in July 2026
        VehicleFuelLog::create([
            'vehicle_id'  => $vehicle1->id,
            'fuel_type'   => 'Pertalite',
            'fuel_liters' => 35.5,
            'fuel_cost'   => 355000,
            'odometer'    => 54000,
            'recorded_at' => '2026-07-15 12:30:00',
            'recorded_by' => $this->ga->id,
        ]);

        $response = $this->actingAs($this->ga)->get(route('vehicles.dashboard', [
            'period' => 'monthly',
            'date'   => '2026-07',
        ]));

        $response->assertOk();

        // Strictly: in_use_vehicles_count is 1 (only vehicle 1), NOT 2!
        $response->assertViewHas('inUseVehiclesCount', 1);
        $response->assertViewHas('availableVehicles', 1); // vehicle 2 is available right now
        $response->assertViewHas('onDutyDriversCount', 1);
        $response->assertViewHas('availableDrivers', 1);

        // Period statistics: approvedInPeriod is 2 (both trips approved in July 2026)
        $response->assertViewHas('approvedInPeriod', 2);
        $response->assertViewHas('totalFuelLiters', 35.5);

        Carbon::setTestNow();
    }

    /**
     * Global Admin Dashboard Section 5 Vehicle overview behaves independently
     * while preserving other sections' filters.
     */
    public function test_global_dashboard_vehicle_section_independent_filter(): void
    {
        $now = Carbon::create(2026, 8, 10, 11, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $vehicle = Vehicle::create([
            'plate_number' => 'B 5555 GLB',
            'name'         => 'Toyota Fortuner',
            'status'       => 'available',
        ]);

        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202608-0001',
            'user_id'        => $this->user->id,
            'vehicle_id'     => $vehicle->id,
            'purpose'        => 'Global Dashboard Test',
            'destination'    => 'Jakarta Pusat',
            'start_datetime' => '2026-08-10 10:00:00',
            'end_datetime'   => '2026-08-10 14:00:00',
            'status'         => 'approved',
            'approved_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.home', [
            'mr_period'  => 'daily',
            'mr_date'    => '2026-08-10',
            'ti_period'  => 'weekly',
            'ti_date'    => '2026-08-10',
            'vhc_period' => 'monthly',
            'vhc_date'   => '2026-08',
        ]));

        $response->assertOk();
        $response->assertViewHas('meetingRoomOverview');
        $response->assertViewHas('toiletInspectionOverview');
        $response->assertViewHas('vehicleOverview');

        $vhcData = $response->viewData('vehicleOverview');
        $this->assertEquals('monthly', $vhcData['period']);
        $this->assertEquals('2026-08', $vhcData['date_input']);
        $this->assertEquals(1, $vhcData['total_requests']);
        $this->assertEquals(1, $vhcData['in_use_now']); // because 10:00 <= 11:00 < 14:00

        $response->assertSee('Ringkasan Kendaraan');
        $response->assertSee('Ringkasan Alat Tulis Kantor');
        $response->assertSee('Ringkasan Surat Izin Keluar (SIK)');
        $response->assertSee('B 5555 GLB');

        $atkData = $response->viewData('atkOverview');
        $this->assertEquals('monthly', $atkData['period']);
        $this->assertNotNull($atkData['start_date']);
        $this->assertNotNull($atkData['end_date']);

        $sikData = $response->viewData('sikOverview');
        $this->assertEquals('monthly', $sikData['period']);
        $this->assertNotNull($sikData['start_date']);
        $this->assertNotNull($sikData['end_date']);

        Carbon::setTestNow();
    }

    /**
     * Test Global Dashboard ATK and SIK sections respond correctly to their independent filters.
     */
    public function test_global_dashboard_atk_and_sik_independent_filters(): void
    {
        $now = Carbon::create(2026, 9, 15, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Create ATK item & variant
        $category = AtkCategory::create(['name' => 'Kertas']);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Kertas A4', 'unit' => 'rim']);
        AtkVariant::create([
            'item_id' => $item->id,
            'name' => '70gsm',
            'sku' => 'KRT-A4-70',
            'unit' => 'rim',
            'stock' => 100,
            'minimum_stock' => 20,
            'is_active' => true,
        ]);

        // Request in September 2026
        $reqSept = AtkRequest::create([
            'request_number' => 'REQ-ATK-202609-0001',
            'user_id' => $this->user->id,
            'status' => 'diajukan',
        ]);
        $reqSept->created_at = Carbon::create(2026, 9, 10, 9, 0, 0, 'Asia/Jakarta');
        $reqSept->save();

        // Request in August 2026
        $reqAug = AtkRequest::create([
            'request_number' => 'REQ-ATK-202608-0002',
            'user_id' => $this->user->id,
            'status' => 'diajukan',
        ]);
        $reqAug->created_at = Carbon::create(2026, 8, 10, 9, 0, 0, 'Asia/Jakarta');
        $reqAug->save();

        // SIK in September 2026
        ExitPermit::create([
            'sik_number' => 'SIK-202609-0001',
            'user_id' => $this->user->id,
            'department' => 'IT',
            'purpose_type' => 'dinas',
            'purpose' => 'September SIK',
            'permit_date' => '2026-09-15',
            'expected_departure_time' => '10:00',
            'expected_return_time' => '12:00',
            'status' => ExitPermit::STATUS_PENDING_APPROVAL,
        ]);

        // SIK in August 2026
        ExitPermit::create([
            'sik_number' => 'SIK-202608-0002',
            'user_id' => $this->user->id,
            'department' => 'GA',
            'purpose_type' => 'pribadi',
            'purpose' => 'August SIK',
            'permit_date' => '2026-08-20',
            'expected_departure_time' => '10:00',
            'expected_return_time' => '12:00',
            'status' => ExitPermit::STATUS_PENDING_APPROVAL,
        ]);

        // Request with September for ATK, August for SIK
        $response = $this->actingAs($this->admin)->get(route('admin.home', [
            'atk_period' => 'monthly',
            'atk_date'   => '2026-09',
            'sik_period' => 'monthly',
            'sik_date'   => '2026-08',
        ]));

        $response->assertOk();
        $atkData = $response->viewData('atkOverview');
        $sikData = $response->viewData('sikOverview');

        // ATK should see September request in period (count = 1)
        $this->assertEquals(1, $atkData['pending_requests']);
        $this->assertTrue($atkData['recent_requests']->contains('request_number', 'REQ-ATK-202609-0001'));

        // SIK should see August permit in period (count = 1)
        $this->assertEquals(1, $sikData['total_permits']);
        $this->assertTrue($sikData['recent_requests']->contains('sik_number', 'SIK-202608-0002'));

        Carbon::setTestNow();
    }
}
