<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleFuelConsumptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $ga;
    protected User $regularUser;
    protected User $unauthorizedUser;
    protected Vehicle $avanza;
    protected Vehicle $triton;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'   => 'Admin Armada',
            'role'   => 'admin',
            'status' => 'active',
        ]);

        $this->ga = User::factory()->create([
            'name'   => 'GA Officer',
            'role'   => 'ga',
            'status' => 'active',
        ]);
        $this->ga->modules()->create(['module' => 'vehicle']);

        $this->regularUser = User::factory()->create([
            'name'   => 'Karyawan Biasa',
            'role'   => 'user',
            'status' => 'active',
        ]);
        $this->regularUser->modules()->create(['module' => 'vehicle']);

        $this->unauthorizedUser = User::factory()->create([
            'name'   => 'User Tanpa Modul Vehicle',
            'role'   => 'user',
            'status' => 'active',
        ]);
        $this->unauthorizedUser->modules()->create(['module' => 'meeting_room']);

        $this->avanza = Vehicle::create([
            'plate_number' => 'BL 1234 AB',
            'name'         => 'Toyota Avanza',
            'fuel_type'    => 'Bensin',
            'status'       => 'available',
        ]);

        $this->triton = Vehicle::create([
            'plate_number' => 'BL 5678 CD',
            'name'         => 'Mitsubishi Triton',
            'fuel_type'    => 'Solar',
            'status'       => 'available',
        ]);
    }

    /** @test */
    public function unauthorized_user_and_regular_user_cannot_manage_fuel_logs()
    {
        // 1. User without vehicle module
        $this->actingAs($this->unauthorizedUser);
        $response = $this->get(route('vehicles.fuel.index'));
        $response->assertForbidden();

        // 2. Regular user with vehicle module (cannot access operational fuel management)
        $this->actingAs($this->regularUser);
        $response2 = $this->get(route('vehicles.fuel.index'));
        $response2->assertForbidden();

        $responseCreate = $this->get(route('vehicles.fuel.create'));
        $responseCreate->assertForbidden();

        $responseStore = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => now()->toDateTimeString(),
            'odometer'    => 20000,
            'fuel_liters' => 40,
        ]);
        $responseStore->assertForbidden();
    }

    /** @test */
    public function ga_can_view_index_and_create_fuel_log()
    {
        $this->actingAs($this->ga);

        $responseIndex = $this->get(route('vehicles.fuel.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Konsumsi BBM');

        $responseStore = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 45.5,
            'fuel_cost'   => 450000,
            'notes'       => 'Pengisian awal operasional kantor',
        ]);

        $responseStore->assertRedirect(route('vehicles.fuel.index'));
        $responseStore->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_fuel_logs', [
            'vehicle_id'  => $this->avanza->id,
            'odometer'    => 20000,
            'fuel_liters' => 45.50,
            'fuel_cost'   => 450000.00,
            'recorded_by' => $this->ga->id,
        ]);
    }

    /** @test */
    public function admin_can_view_fuel_logs_and_history_monitoring_only()
    {
        $this->actingAs($this->admin);

        $log = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 10000,
            'fuel_liters' => 30,
            'recorded_by' => $this->ga->id,
        ]);

        // 1. Admin can view index page
        $responseIndex = $this->get(route('vehicles.fuel.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Konsumsi BBM');
        $responseIndex->assertSee('10.000 km');
        // Admin should see history link, but NOT create/edit/delete buttons
        $responseIndex->assertSee(route('vehicles.fuel.history', $this->avanza));
        $responseIndex->assertDontSee('Tambah Pengisian BBM');
        $responseIndex->assertDontSee(route('vehicles.fuel.edit', $log));
        $responseIndex->assertDontSee(route('vehicles.fuel.destroy', $log));

        // 2. Admin can view history page
        $responseHistory = $this->get(route('vehicles.fuel.history', $this->avanza));
        $responseHistory->assertOk();
        $responseHistory->assertSee('Histori BBM: Toyota Avanza');
        $responseHistory->assertDontSee('Tambah Pengisian');
        $responseHistory->assertDontSee(route('vehicles.fuel.edit', $log));
        $responseHistory->assertDontSee(route('vehicles.fuel.destroy', $log));
    }

    /** @test */
    public function admin_cannot_access_create_store_edit_update_or_delete_fuel_log()
    {
        $this->actingAs($this->admin);

        $log = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 10000,
            'fuel_liters' => 30,
            'recorded_by' => $this->ga->id,
        ]);

        // 1. Cannot access create form
        $responseCreate = $this->get(route('vehicles.fuel.create'));
        $responseCreate->assertForbidden();

        // 2. Cannot store new log
        $responseStore = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 09:00:00',
            'odometer'    => 10500,
            'fuel_liters' => 35,
        ]);
        $responseStore->assertForbidden();

        // 3. Cannot access edit form
        $responseEdit = $this->get(route('vehicles.fuel.edit', $log));
        $responseEdit->assertForbidden();

        // 4. Cannot update log
        $responseUpdate = $this->put(route('vehicles.fuel.update', $log), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 10050,
            'fuel_liters' => 32,
        ]);
        $responseUpdate->assertForbidden();

        // 5. Cannot delete log
        $responseDelete = $this->delete(route('vehicles.fuel.destroy', $log));
        $responseDelete->assertForbidden();

        $this->assertDatabaseHas('vehicle_fuel_logs', ['id' => $log->id]);
    }

    /** @test */
    public function ga_can_view_create_edit_and_delete_fuel_logs()
    {
        $this->actingAs($this->ga);

        // 1. Create form
        $responseCreate = $this->get(route('vehicles.fuel.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Tambah Pengisian BBM');

        // 2. Store log
        $responseStore = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 10000,
            'fuel_liters' => 30,
        ]);
        $responseStore->assertRedirect(route('vehicles.fuel.index'));

        $log = VehicleFuelLog::first();
        $this->assertNotNull($log);

        // 3. View Edit form
        $responseEdit = $this->get(route('vehicles.fuel.edit', $log));
        $responseEdit->assertOk();
        $responseEdit->assertSee('Edit Pengisian BBM');

        // 4. Update log
        $responseUpdate = $this->put(route('vehicles.fuel.update', $log), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 10050,
            'fuel_liters' => 32,
            'fuel_cost'   => 320000,
        ]);
        $responseUpdate->assertRedirect(route('vehicles.fuel.index'));
        $this->assertDatabaseHas('vehicle_fuel_logs', [
            'id'          => $log->id,
            'odometer'    => 10050,
            'fuel_liters' => 32.00,
        ]);

        // 5. Delete log
        $responseDelete = $this->delete(route('vehicles.fuel.destroy', $log));
        $responseDelete->assertRedirect(route('vehicles.fuel.index'));
        $this->assertDatabaseMissing('vehicle_fuel_logs', ['id' => $log->id]);
    }

    /** @test */
    public function vehicle_must_be_valid_and_exist()
    {
        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => 999999,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 50,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
        $this->assertEquals(0, VehicleFuelLog::count());
    }

    /** @test */
    public function odometer_must_be_integer_and_non_negative()
    {
        $this->actingAs($this->ga);

        $response = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => -50,
            'fuel_liters' => 50,
        ]);

        $response->assertSessionHasErrors('odometer');
    }

    /** @test */
    public function fuel_liters_must_be_greater_than_zero()
    {
        $this->actingAs($this->ga);

        // Zero liters
        $responseZero = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 0,
        ]);
        $responseZero->assertSessionHasErrors('fuel_liters');

        // Negative liters
        $responseNegative = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => 20000,
            'fuel_liters' => -10,
        ]);
        $responseNegative->assertSessionHasErrors('fuel_liters');
    }

    /** @test */
    public function fuel_cost_is_optional_and_cannot_be_negative()
    {
        $this->actingAs($this->ga);

        // 1. Successful without fuel_cost
        $responseNullCost = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 10:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 40,
            'fuel_cost'   => null,
        ]);
        $responseNullCost->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vehicle_fuel_logs', [
            'vehicle_id' => $this->avanza->id,
            'fuel_cost'  => null,
        ]);

        // 2. Fails with negative fuel_cost
        $responseNegativeCost = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 11:00:00',
            'odometer'    => 20100,
            'fuel_liters' => 10,
            'fuel_cost'   => -50000,
        ]);
        $responseNegativeCost->assertSessionHasErrors('fuel_cost');
    }

    /** @test */
    public function odometer_cannot_be_lower_than_previous_log_for_same_vehicle()
    {
        $this->actingAs($this->ga);

        // 1. Initial log: 20.500 km
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 08:00:00',
            'odometer'    => 20500,
            'fuel_liters' => 45,
        ]);

        // 2. Attempt next log with 20.300 km (odometer rolled back)
        $responseReject = $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 15:00:00',
            'odometer'    => 20300,
            'fuel_liters' => 50,
        ]);

        $responseReject->assertSessionHasErrors('odometer');
        $this->assertEquals(1, VehicleFuelLog::count());
    }

    /** @test */
    public function first_fuel_log_has_null_consumption_and_second_log_calculates_correctly()
    {
        $this->actingAs($this->ga);

        // 1. First fill-up: 20.000 km, 50 L
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-10 09:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 50,
        ]);

        $firstLog = VehicleFuelLog::where('odometer', 20000)->first();
        $this->assertNull($firstLog->consumption);

        // 2. Second fill-up: 20.500 km, 50 L
        // Distance = 20.500 - 20.000 = 500 km
        // Consumption = 500 / 50 = 10 km/L
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-17 09:00:00',
            'odometer'    => 20500,
            'fuel_liters' => 50,
        ]);

        $secondLog = VehicleFuelLog::where('odometer', 20500)->first();
        $this->assertEquals(10.0, $secondLog->consumption);

        // 3. Third fill-up: 21.000 km, 40 L
        // Distance = 21.000 - 20.500 = 500 km
        // Consumption = 500 / 40 = 12.5 km/L
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-24 09:00:00',
            'odometer'    => 21000,
            'fuel_liters' => 40,
        ]);

        $thirdLog = VehicleFuelLog::where('odometer', 21000)->first();
        $this->assertEquals(12.5, $thirdLog->consumption);
    }

    /** @test */
    public function previous_odometer_and_consumption_are_strictly_isolated_by_vehicle_id()
    {
        $this->actingAs($this->ga);

        // Avanza 1st log: 20.000 km
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-10 08:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 50,
        ]);

        // Triton 1st log: 50.000 km (at a later date)
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->triton->id,
            'recorded_at' => '2026-09-12 08:00:00',
            'odometer'    => 50000,
            'fuel_liters' => 60,
        ]);

        // Avanza 2nd log: 20.500 km, 50 L
        // Must calculate against Avanza's 20.000 km (NOT Triton's 50.000 km)
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-15 08:00:00',
            'odometer'    => 20500,
            'fuel_liters' => 50,
        ]);

        // Triton 2nd log: 50.400 km, 40 L
        // Must calculate against Triton's 50.000 km: (50.400 - 50.000) / 40 = 400 / 40 = 10 km/L
        $this->post(route('vehicles.fuel.store'), [
            'vehicle_id'  => $this->triton->id,
            'recorded_at' => '2026-09-16 08:00:00',
            'odometer'    => 50400,
            'fuel_liters' => 40,
        ]);

        $avanzaLog2 = VehicleFuelLog::where('vehicle_id', $this->avanza->id)->where('odometer', 20500)->first();
        $this->assertEquals(10.0, $avanzaLog2->consumption);

        $tritonLog2 = VehicleFuelLog::where('vehicle_id', $this->triton->id)->where('odometer', 50400)->first();
        $this->assertEquals(10.0, $tritonLog2->consumption);
    }

    /** @test */
    public function editing_or_deleting_earlier_log_dynamically_recalculates_subsequent_consumption()
    {
        $this->actingAs($this->ga);

        // Log 1: 10.000 km, 50 L
        $log1 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-01 08:00:00',
            'odometer'    => 10000,
            'fuel_liters' => 50,
            'recorded_by' => $this->ga->id,
        ]);

        // Log 2: 10.500 km, 50 L (Distance = 500, km/L = 10.0)
        $log2 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-05 08:00:00',
            'odometer'    => 10500,
            'fuel_liters' => 50,
            'recorded_by' => $this->ga->id,
        ]);

        // Log 3: 11.000 km, 50 L (Distance = 500, km/L = 10.0)
        $log3 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-10 08:00:00',
            'odometer'    => 11000,
            'fuel_liters' => 50,
            'recorded_by' => $this->ga->id,
        ]);

        $this->assertEquals(10.0, $log2->fresh()->consumption);
        $this->assertEquals(10.0, $log3->fresh()->consumption);

        // Delete Log 2: Log 3's previous log dynamically becomes Log 1!
        // Distance becomes: 11.000 - 10.000 = 1000 km
        // New consumption for Log 3: 1000 / 50 = 20.0 km/L
        $this->delete(route('vehicles.fuel.destroy', $log2));

        $this->assertDatabaseMissing('vehicle_fuel_logs', ['id' => $log2->id]);
        $this->assertEquals(20.0, $log3->fresh()->consumption);
    }

    /** @test */
    public function update_validates_odometer_within_bounds_of_previous_and_next_logs()
    {
        $this->actingAs($this->ga);

        $log1 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-01 08:00:00',
            'odometer'    => 10000,
            'fuel_liters' => 50,
            'recorded_by' => $this->ga->id,
        ]);

        $log2 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-05 08:00:00',
            'odometer'    => 10500,
            'fuel_liters' => 50,
            'recorded_by' => $this->ga->id,
        ]);

        $log3 = VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-10 08:00:00',
            'odometer'    => 11000,
            'fuel_liters' => 50,
            'recorded_by' => $this->admin->id,
        ]);

        // Attempt to edit log2 with odometer > log3 (e.g. 11500 > 11000)
        $responseInvalidHigh = $this->put(route('vehicles.fuel.update', $log2), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-05 08:00:00',
            'odometer'    => 11500,
            'fuel_liters' => 50,
        ]);
        $responseInvalidHigh->assertSessionHasErrors('odometer');

        // Attempt to edit log2 with odometer < log1 (e.g. 9500 < 10000)
        $responseInvalidLow = $this->put(route('vehicles.fuel.update', $log2), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-05 08:00:00',
            'odometer'    => 9500,
            'fuel_liters' => 50,
        ]);
        $responseInvalidLow->assertSessionHasErrors('odometer');

        // Valid edit within bounds (10.600 km)
        $responseValid = $this->put(route('vehicles.fuel.update', $log2), [
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-05 08:00:00',
            'odometer'    => 10600,
            'fuel_liters' => 50,
        ]);
        $responseValid->assertSessionHasNoErrors();
        $this->assertEquals(10600, $log2->fresh()->odometer);
    }

    /** @test */
    public function navigation_menu_shows_konsumsi_bbm_for_admin_and_ga_only()
    {
        // 1. Admin
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('vehicles.fuel.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Konsumsi BBM');

        // 2. GA
        $this->actingAs($this->ga);
        $responseGa = $this->get(route('vehicles.fuel.index'));
        $responseGa->assertOk();
        $responseGa->assertSee('Konsumsi BBM');

        // 3. Regular user
        $this->actingAs($this->regularUser);
        $responseUser = $this->get(route('vehicles.requests.index'));
        $responseUser->assertOk();
        $responseUser->assertDontSee('Konsumsi BBM');
    }

    /** @test */
    public function vehicle_fuel_history_page_renders_vehicle_chronology()
    {
        $this->actingAs($this->ga);

        VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-01 08:00:00',
            'odometer'    => 20000,
            'fuel_liters' => 45,
            'recorded_by' => $this->ga->id,
        ]);

        VehicleFuelLog::create([
            'vehicle_id'  => $this->avanza->id,
            'recorded_at' => '2026-09-08 08:00:00',
            'odometer'    => 20450,
            'fuel_liters' => 45,
            'recorded_by' => $this->ga->id,
        ]);

        $response = $this->get(route('vehicles.fuel.history', $this->avanza));
        $response->assertOk();
        $response->assertSee('Histori BBM: Toyota Avanza');
        $response->assertSee('20.450 km');
        $response->assertSee('10 km/L');
    }
}
