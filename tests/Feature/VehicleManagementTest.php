<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $ga;
    protected User $user1;
    protected User $user2;
    protected User $unauthorizedUser;
    protected Vehicle $vehicle1;
    protected Vehicle $vehicle2;
    protected Driver $driver1;
    protected Driver $driver2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->ga = User::factory()->create([
            'name' => 'GA Officer',
            'role' => 'ga',
            'status' => 'active',
        ]);
        $this->ga->modules()->create(['module' => 'vehicle']);

        $this->user1 = User::factory()->create([
            'name' => 'Karyawan Satu',
            'role' => 'user',
            'status' => 'active',
        ]);
        $this->user1->modules()->create(['module' => 'vehicle']);

        $this->user2 = User::factory()->create([
            'name' => 'Karyawan Dua',
            'role' => 'user',
            'status' => 'active',
        ]);
        $this->user2->modules()->create(['module' => 'vehicle']);

        $this->unauthorizedUser = User::factory()->create([
            'name' => 'User No Vehicle Module',
            'role' => 'user',
            'status' => 'active',
        ]);
        $this->unauthorizedUser->modules()->create(['module' => 'meeting_room']);

        $this->vehicle1 = Vehicle::create([
            'plate_number' => 'BL 1111 AA',
            'name' => 'Toyota Avanza',
            'fuel_type' => 'Bensin',
            'status' => 'available',
        ]);

        $this->vehicle2 = Vehicle::create([
            'plate_number' => 'BL 2222 BB',
            'name' => 'Mitsubishi Triton',
            'fuel_type' => 'Solar',
            'status' => 'available',
        ]);

        $this->driver1 = Driver::create([
            'name' => 'Supir Satu',
            'phone_number' => '081234567890',
            'status' => 'available',
        ]);

        $this->driver2 = Driver::create([
            'name' => 'Supir Dua',
            'phone_number' => '089876543210',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function unauthorized_user_without_vehicle_module_cannot_access_vehicles()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->get(route('vehicles.requests.index'));
        $response->assertForbidden();
    }

    /** @test */
    public function admin_and_ga_can_crud_vehicles()
    {
        $this->actingAs($this->admin);

        // 1. Create Vehicle
        $response = $this->post(route('vehicles.store'), [
            'plate_number' => 'bl 3333 cc',
            'name' => 'Toyota Innova',
            'fuel_type' => 'Diesel',
            'status' => 'available',
            'notes' => 'Mobil direksi',
        ]);
        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'BL 3333 CC',
            'name' => 'Toyota Innova',
        ]);

        $newVehicle = Vehicle::where('plate_number', 'BL 3333 CC')->first();

        // 2. Update Vehicle (as GA)
        $this->actingAs($this->ga);
        $response = $this->put(route('vehicles.update', $newVehicle), [
            'plate_number' => 'BL 3333 CC',
            'name' => 'Toyota Innova Reborn',
            'fuel_type' => 'Diesel',
            'status' => 'maintenance',
            'notes' => 'Servis rem',
        ]);
        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'id' => $newVehicle->id,
            'name' => 'Toyota Innova Reborn',
            'status' => 'maintenance',
        ]);

        // 3. Delete Vehicle (as Admin)
        $this->actingAs($this->admin);
        $response = $this->delete(route('vehicles.destroy', $newVehicle));
        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseMissing('vehicles', ['id' => $newVehicle->id]);
    }

    /** @test */
    public function regular_user_cannot_create_update_or_delete_vehicle()
    {
        $this->actingAs($this->user1);

        // Cannot access create form
        $response = $this->get(route('vehicles.create'));
        $response->assertForbidden();

        // Cannot store
        $response = $this->post(route('vehicles.store'), [
            'plate_number' => 'BL 9999 ZZ',
            'name' => 'Honda Civic',
            'fuel_type' => 'Bensin',
            'status' => 'available',
        ]);
        $response->assertForbidden();

        // Cannot edit
        $response = $this->get(route('vehicles.edit', $this->vehicle1));
        $response->assertForbidden();

        // Cannot update
        $response = $this->put(route('vehicles.update', $this->vehicle1), [
            'plate_number' => 'BL 1111 AA',
            'name' => 'Hacked Name',
            'fuel_type' => 'Bensin',
            'status' => 'available',
        ]);
        $response->assertForbidden();

        // Cannot delete
        $response = $this->delete(route('vehicles.destroy', $this->vehicle1));
        $response->assertForbidden();
    }

    /** @test */
    public function admin_and_ga_can_crud_drivers()
    {
        $this->actingAs($this->admin);

        // 1. Create Driver
        $response = $this->post(route('vehicles.drivers.store'), [
            'name' => 'Pak Joko',
            'phone_number' => '081122334455',
            'status' => 'available',
            'notes' => 'Supir kantor Banda Aceh',
        ]);
        $response->assertRedirect(route('vehicles.drivers.index'));
        $this->assertDatabaseHas('drivers', ['name' => 'Pak Joko']);

        $driver = Driver::where('name', 'Pak Joko')->first();

        // 2. Update Driver (as GA)
        $this->actingAs($this->ga);
        $response = $this->put(route('vehicles.drivers.update', $driver), [
            'name' => 'Pak Joko Susilo',
            'phone_number' => '081122334455',
            'status' => 'inactive',
        ]);
        $response->assertRedirect(route('vehicles.drivers.index'));
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'name' => 'Pak Joko Susilo',
            'status' => 'inactive',
        ]);

        // 3. Delete Driver (as Admin)
        $this->actingAs($this->admin);
        $response = $this->delete(route('vehicles.drivers.destroy', $driver));
        $response->assertRedirect(route('vehicles.drivers.index'));
        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    /** @test */
    public function regular_user_cannot_create_update_or_delete_driver()
    {
        $this->actingAs($this->user1);

        $response = $this->get(route('vehicles.drivers.create'));
        $response->assertForbidden();

        $response = $this->post(route('vehicles.drivers.store'), [
            'name' => 'Unauthorized Driver',
            'phone_number' => '0899999999',
            'status' => 'available',
        ]);
        $response->assertForbidden();

        $response = $this->get(route('vehicles.drivers.edit', $this->driver1));
        $response->assertForbidden();

        $response = $this->put(route('vehicles.drivers.update', $this->driver1), [
            'name' => 'Hacked Driver',
            'phone_number' => '0899999999',
            'status' => 'available',
        ]);
        $response->assertForbidden();

        $response = $this->delete(route('vehicles.drivers.destroy', $this->driver1));
        $response->assertForbidden();
    }

    /** @test */
    public function regular_user_can_submit_vehicle_request()
    {
        $this->actingAs($this->user1);

        $start = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(8)->setMinute(0)->setSecond(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(12)->setMinute(0)->setSecond(0);

        $response = $this->post(route('vehicles.requests.store'), [
            'purpose' => 'Meeting dengan vendor semen di Banda Aceh',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start->format('Y-m-d\TH:i'),
            'end_datetime' => $end->format('Y-m-d\TH:i'),
            'vehicle_id' => $this->vehicle1->id,
            'notes' => 'Membawa 3 orang staf finance',
        ]);

        $response->assertRedirect(route('vehicles.requests.index'));
        $this->assertDatabaseHas('vehicle_requests', [
            'user_id' => $this->user1->id,
            'purpose' => 'Meeting dengan vendor semen di Banda Aceh',
            'destination' => 'Banda Aceh',
            'status' => 'pending_approval',
        ]);

        // Notification sent to GA (operational approver)
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ga->id,
            'title' => 'Permohonan Kendaraan Baru',
        ]);
    }

    /** @test */
    public function regular_user_cannot_submit_request_with_end_before_start()
    {
        $this->actingAs($this->user1);

        $start = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(10)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(9)->setMinute(0);

        $response = $this->post(route('vehicles.requests.store'), [
            'purpose' => 'Testing invalid time',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start->format('Y-m-d\TH:i'),
            'end_datetime' => $end->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors('end_datetime');
    }

    /** @test */
    public function privacy_regular_user_only_sees_own_requests()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(11)->setMinute(0);

        // User 1's request
        $req1 = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00101',
            'user_id' => $this->user1->id,
            'purpose' => 'Kunjungan User 1',
            'destination' => 'Krueng Raba',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        // User 2's request
        $req2 = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00102',
            'user_id' => $this->user2->id,
            'purpose' => 'Kunjungan User 2',
            'destination' => 'Lhoknga',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        // Acting as User 1
        $this->actingAs($this->user1);

        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();
        $response->assertSee('REQ-VHC-202609-00101');
        $response->assertDontSee('REQ-VHC-202609-00102');

        // User 1 cannot view User 2's request detail
        $response = $this->get(route('vehicles.requests.show', $req2));
        $response->assertForbidden();

        // User 1 can view own request detail
        $response = $this->get(route('vehicles.requests.show', $req1));
        $response->assertOk();
    }

    /** @test */
    public function admin_and_ga_can_view_all_requests()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(11)->setMinute(0);

        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00201',
            'user_id' => $this->user1->id,
            'purpose' => 'Kunjungan User 1',
            'destination' => 'Krueng Raba',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00202',
            'user_id' => $this->user2->id,
            'purpose' => 'Kunjungan User 2',
            'destination' => 'Lhoknga',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        // GA can view index and see both
        $this->actingAs($this->ga);
        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();
        $response->assertSee('REQ-VHC-202609-00201');
        $response->assertSee('REQ-VHC-202609-00202');
    }

    /** @test */
    public function requester_can_cancel_own_pending_request()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(11)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00301',
            'user_id' => $this->user1->id,
            'purpose' => 'Rapat batal',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->user1);
        $response = $this->post(route('vehicles.requests.cancel', $req));
        $response->assertRedirect(route('vehicles.requests.index'));

        $this->assertDatabaseHas('vehicle_requests', [
            'id' => $req->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function requester_cannot_cancel_others_request_or_approved_request()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(11)->setMinute(0);

        // User 2's request
        $reqUser2 = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00302',
            'user_id' => $this->user2->id,
            'purpose' => 'Rapat penting',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        // User 1 tries to cancel User 2's request
        $this->actingAs($this->user1);
        $response = $this->post(route('vehicles.requests.cancel', $reqUser2));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending_approval', $reqUser2->fresh()->status);

        // Approved request cannot be cancelled by user
        $reqUser1Approved = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00303',
            'user_id' => $this->user1->id,
            'purpose' => 'Rapat selesai approved',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'approved',
            'vehicle_id' => $this->vehicle1->id,
        ]);

        $response = $this->post(route('vehicles.requests.cancel', $reqUser1Approved));
        $response->assertSessionHasErrors('error');
        $this->assertEquals('approved', $reqUser1Approved->fresh()->status);
    }

    /** @test */
    public function ga_can_approve_request_with_vehicle_and_driver()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00401',
            'user_id' => $this->user1->id,
            'purpose' => 'Kunjungan dinas pabrik',
            'destination' => 'Lhoknga',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
        ]);

        $response->assertRedirect(route('vehicles.requests.show', $req->id));

        $this->assertDatabaseHas('vehicle_requests', [
            'id' => $req->id,
            'status' => 'approved',
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
            'approved_by' => $this->ga->id,
        ]);

        // Notification to requester with allocated fleet info
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user1->id,
            'title' => 'Permohonan Kendaraan Disetujui',
        ]);
    }

    /** @test */
    public function non_ga_including_admin_and_user_cannot_approve_or_reject_requests()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00402',
            'user_id' => $this->user1->id,
            'purpose' => 'Kunjungan dinas pabrik',
            'destination' => 'Lhoknga',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        // 1. Regular user tries to approve -> Forbidden
        $this->actingAs($this->user2);
        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
        ]);
        $response->assertForbidden();

        // 2. Admin tries to approve -> Forbidden (GA is sole operational approver)
        $this->actingAs($this->admin);
        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
        ]);
        $response->assertForbidden();

        // 3. Admin tries to reject -> Forbidden
        $response = $this->post(route('vehicles.requests.reject', $req), [
            'rejection_reason' => 'Admin trying to reject',
        ]);
        $response->assertForbidden();

        $this->assertEquals('pending_approval', $req->fresh()->status);
    }

    /** @test */
    public function ga_can_reject_request_with_reason()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(4)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(4)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00601',
            'user_id' => $this->user1->id,
            'purpose' => 'Permohonan ditolak',
            'destination' => 'Krueng Raba',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.requests.reject', $req), [
            'rejection_reason' => 'Unit kendaraan sedang dialokasikan untuk audit keselamatan.',
        ]);

        $response->assertRedirect(route('vehicles.requests.show', $req->id));

        $this->assertDatabaseHas('vehicle_requests', [
            'id' => $req->id,
            'status' => 'rejected',
            'rejection_reason' => 'Unit kendaraan sedang dialokasikan untuk audit keselamatan.',
            'approved_by' => $this->ga->id,
        ]);

        // Notification to requester
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user1->id,
            'title' => 'Permohonan Kendaraan Ditolak',
        ]);
    }

    /** @test */
    public function double_booking_prevention_for_overlapping_approved_requests()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(12)->setMinute(0);

        // Already approved request with vehicle1 and driver1
        VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00501',
            'user_id' => $this->user1->id,
            'purpose' => 'Sesi pertama',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
            'status' => 'approved',
            'approved_by' => $this->ga->id,
        ]);

        // Overlapping pending request (10:00 - 14:00)
        $overlapStart = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(10)->setMinute(0);
        $overlapEnd = Carbon::now('Asia/Jakarta')->addDays(3)->setHour(14)->setMinute(0);

        $reqConflict = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00502',
            'user_id' => $this->user2->id,
            'purpose' => 'Sesi kedua bentrok',
            'destination' => 'Banda Aceh',
            'start_datetime' => $overlapStart,
            'end_datetime' => $overlapEnd,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        // Try to approve with the same vehicle -> Must fail
        $response = $this->post(route('vehicles.requests.approve', $reqConflict), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver2->id,
        ]);
        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending_approval', $reqConflict->fresh()->status);

        // Try to approve with the same driver -> Must fail
        $response = $this->post(route('vehicles.requests.approve', $reqConflict), [
            'vehicle_id' => $this->vehicle2->id,
            'driver_id' => $this->driver1->id,
        ]);
        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending_approval', $reqConflict->fresh()->status);

        // Approving with alternative vehicle & driver -> Must succeed
        $response = $this->post(route('vehicles.requests.approve', $reqConflict), [
            'vehicle_id' => $this->vehicle2->id,
            'driver_id' => $this->driver2->id,
        ]);
        $response->assertRedirect(route('vehicles.requests.show', $reqConflict->id));
        $this->assertEquals('approved', $reqConflict->fresh()->status);
        $this->assertEquals($this->vehicle2->id, $reqConflict->fresh()->vehicle_id);
        $this->assertEquals($this->driver2->id, $reqConflict->fresh()->driver_id);
    }

    /** @test */
    public function vehicle_maintenance_or_inactive_cannot_be_allocated()
    {
        $maintenanceVehicle = Vehicle::create([
            'plate_number' => 'BL 5555 MM',
            'name' => 'Toyota HiAce',
            'fuel_type' => 'Diesel',
            'status' => 'maintenance',
        ]);

        $start = Carbon::now('Asia/Jakarta')->addDays(5)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(5)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00901',
            'user_id' => $this->user1->id,
            'purpose' => 'Test mobil rusak',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $maintenanceVehicle->id,
            'driver_id' => $this->driver1->id,
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending_approval', $req->fresh()->status);
    }

    /** @test */
    public function driver_inactive_cannot_be_allocated()
    {
        $inactiveDriver = Driver::create([
            'name' => 'Supir Cuti',
            'phone_number' => '087777777777',
            'status' => 'inactive',
        ]);

        $start = Carbon::now('Asia/Jakarta')->addDays(5)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(5)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00902',
            'user_id' => $this->user1->id,
            'purpose' => 'Test supir cuti',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $inactiveDriver->id,
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending_approval', $req->fresh()->status);
    }

    /** @test */
    public function automatic_completion_via_server_side_mechanism_when_end_datetime_elapsed()
    {
        // Request that has reached end_datetime in the past
        $pastReq = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00903',
            'user_id' => $this->user1->id,
            'purpose' => 'Trip kemarin',
            'destination' => 'Banda Aceh',
            'start_datetime' => Carbon::now('Asia/Jakarta')->subHours(4),
            'end_datetime' => Carbon::now('Asia/Jakarta')->subHour(),
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => $this->driver1->id,
            'status' => 'approved',
            'approved_by' => $this->ga->id,
        ]);

        // Request that is still ongoing in the future
        $futureReq = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00904',
            'user_id' => $this->user2->id,
            'purpose' => 'Trip besok',
            'destination' => 'Lhoknga',
            'start_datetime' => Carbon::now('Asia/Jakarta')->addHours(1),
            'end_datetime' => Carbon::now('Asia/Jakarta')->addHours(4),
            'vehicle_id' => $this->vehicle2->id,
            'driver_id' => $this->driver2->id,
            'status' => 'approved',
            'approved_by' => $this->ga->id,
        ]);

        // Run server-side auto completion command
        $this->artisan('vehicles:complete-expired')->assertExitCode(0);

        $this->assertEquals('completed', $pastReq->fresh()->status);
        $this->assertEquals('approved', $futureReq->fresh()->status);
    }

    /** @test */
    public function vehicle_views_render_correct_module_identity_and_do_not_leak_meeting_room_elements()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();

        // Must display vehicle module identity
        $response->assertSee('Manajemen Kendaraan');

        // Must NOT leak meeting room legacy elements
        $response->assertDontSee('+ Tambah Ruangan');
        $response->assertDontSee('Reservasi Ruang Meeting');
    }

    /** @test */
    public function ui_displays_approval_for_ga_and_neutral_card_without_manual_completion_for_others()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(9)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(2)->setHour(12)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00801',
            'user_id' => $this->user1->id,
            'purpose' => 'Audit UI roles',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'vehicle_id' => $this->vehicle1->id,
            'status' => 'pending_approval',
        ]);

        // 1. GA viewing pending request -> Sees approval & allocation form
        $this->actingAs($this->ga);
        $response = $this->get(route('vehicles.requests.show', $req));
        $response->assertOk();
        $response->assertSee('Persetujuan & Penugasan Armada (GA)', false);
        $response->assertSee('Setujui & Tugaskan', false);
        $response->assertSee('Tolak Permohonan', false);
        $response->assertDontSee('Tandai Selesai');

        // 2. Admin viewing pending request -> Sees neutral card, no approval form
        $this->actingAs($this->admin);
        $response = $this->get(route('vehicles.requests.show', $req));
        $response->assertOk();
        $response->assertSee('Menunggu Proses Persetujuan dan Alokasi Armada');
        $response->assertDontSee('Setujui & Tugaskan');
        $response->assertDontSee('Tandai Selesai');

        // 3. Requester viewing pending request -> Sees neutral card, can cancel
        $this->actingAs($this->user1);
        $response = $this->get(route('vehicles.requests.show', $req));
        $response->assertOk();
        $response->assertSee('Menunggu Proses Persetujuan dan Alokasi Armada');
        $response->assertSee('Batalkan Permohonan');
        $response->assertDontSee('Setujui & Tugaskan');
        $response->assertDontSee('Tandai Selesai');
    }

    /** @test */
    public function admin_sees_monitoring_view_without_ajukan_kendaraan_button_and_without_operational_copy()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();

        // 1. Must see Admin-specific monitoring title & copy
        $response->assertSee('Monitoring Permohonan Kendaraan');
        $response->assertSee('Pantau seluruh data permohonan kendaraan operasional');

        // 2. Must NOT see requester button "+ Ajukan Kendaraan"
        $response->assertDontSee('Ajukan Kendaraan');

        // 3. Must NOT see operational approver copy ("proses pengajuan")
        $response->assertDontSee('proses pengajuan');

        // 4. Must NOT see "Permohonan Saya Sendiri" filter option
        $response->assertDontSee('Permohonan Saya Sendiri');
    }

    /** @test */
    public function admin_and_ga_cannot_access_create_or_store_vehicle_request()
    {
        // 1. Admin cannot view create form or store request
        $this->actingAs($this->admin);
        $response = $this->get(route('vehicles.requests.create'));
        $response->assertForbidden();

        $start = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(8)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(12)->setMinute(0);

        $response = $this->post(route('vehicles.requests.store'), [
            'purpose' => 'Admin mencoba pinjam mobil',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start->format('Y-m-d\TH:i'),
            'end_datetime' => $end->format('Y-m-d\TH:i'),
        ]);
        $response->assertForbidden();

        // 2. GA cannot view create form or store request
        $this->actingAs($this->ga);
        $response = $this->get(route('vehicles.requests.create'));
        $response->assertForbidden();

        $response = $this->post(route('vehicles.requests.store'), [
            'purpose' => 'GA mencoba pinjam mobil',
            'destination' => 'Banda Aceh',
            'start_datetime' => $start->format('Y-m-d\TH:i'),
            'end_datetime' => $end->format('Y-m-d\TH:i'),
        ]);
        $response->assertForbidden();
    }

    /** @test */
    public function ga_and_regular_user_see_their_respective_appropriate_views()
    {
        // 1. GA sees operational approval copy, but NO "+ Ajukan Kendaraan" and NO "Permohonan Saya Sendiri"
        $this->actingAs($this->ga);
        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();
        $response->assertSee('Daftar Permohonan Kendaraan');
        $response->assertSee('proses persetujuan');
        $response->assertDontSee('Ajukan Kendaraan');
        $response->assertDontSee('Permohonan Saya Sendiri');

        // 2. Regular user sees requester copy and "+ Ajukan Kendaraan" button, without user filter
        $this->actingAs($this->user1);
        $response = $this->get(route('vehicles.requests.index'));
        $response->assertOk();
        $response->assertSee('Permohonan Kendaraan Saya');
        $response->assertSee('Ajukan Kendaraan');
        $response->assertDontSee('Permohonan Saya Sendiri');
    }

    /** @test */
    public function ga_can_approve_request_with_self_drive_lepas_kunci_without_driver()
    {
        $start = Carbon::now('Asia/Jakarta')->addDays(4)->setHour(8)->setMinute(0);
        $end = Carbon::now('Asia/Jakarta')->addDays(4)->setHour(17)->setMinute(0);

        $req = VehicleRequest::create([
            'request_number' => 'REQ-VHC-202609-00999',
            'user_id' => $this->user1->id,
            'purpose' => 'Dinas mandiri lepas kunci',
            'destination' => 'Lhoknga - Calang',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($this->ga);

        // Approve without driver_id (self-drive / lepas kunci)
        $response = $this->post(route('vehicles.requests.approve', $req), [
            'vehicle_id' => $this->vehicle1->id,
            'driver_id' => '',
        ]);

        $response->assertRedirect(route('vehicles.requests.show', $req->id));

        $freshReq = $req->fresh();
        $this->assertEquals('approved', $freshReq->status);
        $this->assertEquals($this->vehicle1->id, $freshReq->vehicle_id);
        $this->assertNull($freshReq->driver_id);
        $this->assertEquals($this->ga->id, $freshReq->approved_by);

        // Verify that Show and Index views display Lepas Kunci badge
        $showRes = $this->get(route('vehicles.requests.show', $req->id));
        $showRes->assertSee('Lepas Kunci (Tanpa Supir)');

        $indexRes = $this->get(route('vehicles.requests.index'));
        $indexRes->assertSee('Lepas Kunci (Tanpa Supir)');
    }
}

