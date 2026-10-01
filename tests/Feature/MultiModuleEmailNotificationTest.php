<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use App\Models\AtkRequest;
use App\Models\ExitPermit;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use App\Models\ToiletLocation;
use App\Models\ChecklistItem;
use App\Models\Inspection;
use App\Models\SmtpSetting;
use App\Models\EmailTemplate;
use App\Models\EmailNotificationSetting;
use App\Services\EmailService;
use App\Services\SmtpSettingsService;
use App\Services\BookingService;
use App\Services\AtkRequestService;
use App\Services\SikService;
use App\Services\VehicleRequestService;
use App\Services\InspectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MultiModuleEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed default templates & notification settings
        $this->artisan('migrate');
    }

    public function test_can_save_and_update_unauthenticated_port_25_smtp_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Create Port 25 setting without password
        $response = $this->actingAs($admin)->post(route('admin.settings.smtp.update'), [
            'host' => '10.10.1.25',
            'port' => 25,
            'no_auth' => 1,
            'username' => '',
            'password' => '',
            'encryption' => 'none',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'SBA Notification Relay',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('smtp_settings', [
            'host' => '10.10.1.25',
            'port' => 25,
            'username' => null,
            'password' => null,
            'encryption' => null,
            'from_address' => 'noreply@sba.co.id',
        ]);

        $setting = SmtpSetting::where('host', '10.10.1.25')->first();
        $this->assertNull($setting->password);
        $this->assertNull($setting->username);

        // 2. Activate this setting
        $activateResponse = $this->actingAs($admin)->post(route('admin.settings.smtp.activate', $setting->id));
        $activateResponse->assertRedirect();
        $this->assertTrue($setting->fresh()->is_active);
    }

    public function test_meeting_hybrid_sends_email_to_ga_and_it(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user', 'department' => 'IT']);
        $gaUser = User::factory()->create(['role' => 'ga', 'email' => 'ga@sba.co.id']);
        $itUser = User::factory()->create(['role' => 'it', 'email' => 'it@sba.co.id']);
        $room = Room::create([
            'name' => 'Ruang Rapat Utama',
            'location' => 'Lantai 2 Gedung Admin',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $bookingService = app(BookingService::class);
        $booking = $bookingService->createBooking($user, [
            'room_id' => $room->id,
            'meeting_type' => 'hybrid',
            'title' => 'Weekly Sync Meeting SBA',
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'participant_count' => 10,
            'additional_requirements' => ['Layar Proyektor'],
        ]);

        // Assert mail sent to GA for room preparation
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($gaUser) {
            return $mail->hasTo($gaUser->email);
        });

        // Assert mail sent to IT for technical preparation
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($itUser) {
            return $mail->hasTo($itUser->email);
        });

        // Assert mail sent to requester
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_atk_workflow_sends_emails_to_requester_and_ga(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user', 'email' => 'pemohon@sba.co.id']);
        $gaUser = User::factory()->create(['role' => 'ga', 'email' => 'ga_atk@sba.co.id']);

        $category = AtkCategory::create(['name' => 'Kertas', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'HVS A4', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'name' => '70gsm',
            'sku' => 'HVS-70',
            'unit' => 'rim',
            'stock' => 50,
            'minimum_stock' => 5,
            'purchase_price' => 45000,
            'is_active' => true,
        ]);

        $atkService = app(AtkRequestService::class);

        // 1. Create request
        $this->actingAs($user);
        $request = $atkService->createRequest($user->id, [
            ['variant_id' => $variant->id, 'quantity' => 2],
        ], 'Kebutuhan dokumen bulanan');

        // Mail to requester
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Mail to GA
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($gaUser) {
            return $mail->hasTo($gaUser->email);
        });

        // 2. Approve request
        $atkService->approveRequest($request->id, $gaUser->id);
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_sik_workflow_sends_emails_to_requester_approver_and_security(): void
    {
        Mail::fake();

        $approver = User::factory()->create(['role' => 'user', 'department' => 'IT', 'email' => 'hod_it@sba.co.id', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user', 'department' => 'IT', 'email' => 'staff_it@sba.co.id', 'status' => 'active']);
        $security = User::factory()->create(['role' => 'security', 'email' => 'security@sba.co.id', 'status' => 'active']);

        // Setup department approver
        \App\Models\DepartmentApproval::create([
            'department_name' => 'IT',
            'hod_user_id' => $approver->id,
        ]);

        $sikService = app(SikService::class);

        // 1. Submit SIK immediately
        $permit = $sikService->createPermit($user, [
            'purpose_type' => 'dinas',
            'purpose' => 'Meeting koordinasi ke kantor SIG Pusat',
            'permit_date' => now()->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '12:00',
            'vehicle_type' => 'company_vehicle',
            'vehicle_details' => 'Avanza BL 1234 AB',
            'action' => 'submit',
        ], true);

        // Mail to requester confirmation
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Mail to approver
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($approver) {
            return $mail->hasTo($approver->email);
        });

        // 2. Approve SIK
        $sikService->approve($permit, $approver, 'Disetujui untuk dinas resmi');

        // Mail to requester approved
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Mail to security
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($security) {
            return $mail->hasTo($security->email);
        });
    }

    public function test_vehicle_workflow_sends_emails_to_requester_ga_and_driver(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user', 'email' => 'requester_mobil@sba.co.id']);
        $gaUser = User::factory()->create(['role' => 'ga', 'email' => 'ga_transport@sba.co.id']);
        $driverUser = User::factory()->create(['role' => 'user', 'email' => 'supir@sba.co.id']);
        
        $vehicle = Vehicle::create([
            'name' => 'Innova Reborn',
            'plate_number' => 'BL 8888 SBA',
            'type' => 'minibus',
            'capacity' => 7,
            'status' => 'available',
        ]);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'name' => 'Pak Joko',
            'phone_number' => '08123456789',
            'status' => 'available',
        ]);

        $vehicleService = app(VehicleRequestService::class);

        // 1. Create request
        $vRequest = $vehicleService->createRequest($user, [
            'purpose' => 'Antar tamu vendor ke Bandara SIM',
            'destination' => 'Bandara Sultan Iskandar Muda',
            'start_datetime' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addHours(5)->format('Y-m-d H:i:s'),
        ]);

        // Mail to requester
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Mail to GA Transport
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($gaUser) {
            return $mail->hasTo($gaUser->email);
        });

        // 2. Approve and assign driver
        $vehicleService->approveRequest($vRequest, $gaUser, $vehicle->id, $driver->id);

        // Mail to requester approved
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Mail to driver assignment
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($driverUser) {
            return $mail->hasTo($driverUser->email);
        });
    }

    public function test_toilet_inspection_sends_emails_to_inspector_and_supervisor_on_issue(): void
    {
        Mail::fake();

        $inspector = User::factory()->create(['role' => 'user', 'email' => 'inspector@sba.co.id']);
        $spv = User::factory()->create(['role' => 'spv', 'email' => 'spv_toilet@sba.co.id']);

        $location = ToiletLocation::create([
            'toilet_name' => 'Toilet Admin Lt 1',
            'building' => 'Main Office',
            'floor' => '1',
            'area' => 'Kantor Utama',
            'gender' => 'pria',
        ]);

        $itemClean = ChecklistItem::create(['name' => 'Kebersihan Lantai', 'is_active' => true]);
        $itemWastafel = ChecklistItem::create(['name' => 'Wastafel & Keran', 'is_active' => true]);

        $this->actingAs($inspector);
        $inspectionService = app(InspectionService::class);

        // Create inspection with a defect/issue
        $inspection = $inspectionService->createInspection([
            'location_id' => $location->id,
            'status' => 'submitted',
            'notes' => 'Keran wastafel bocor air menetes terus',
            'results' => [
                $itemClean->id => ['is_ok' => true, 'remarks' => 'Lantai kering'],
                $itemWastafel->id => ['is_ok' => false, 'remarks' => 'Kran bocor'],
            ],
        ]);

        // Mail to inspector confirmation
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($inspector) {
            return $mail->hasTo($inspector->email);
        });

        // Mail to supervisor alert for issues
        Mail::assertSent(\App\Mail\TemplateMail::class, function ($mail) use ($spv) {
            return $mail->hasTo($spv->email);
        });
    }
}
