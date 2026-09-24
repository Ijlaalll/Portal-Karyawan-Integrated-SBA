<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\DepartmentApproval;
use App\Models\ExitPermit;
use App\Models\SikApprovalHistory;
use App\Models\SikScanLog;
use App\Models\User;
use App\Models\UserModule;
use App\Services\DepartmentApprovalService;
use App\Services\SikScanService;
use App\Services\SikService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SikManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $itHod;
    protected User $itHodApprover; // Atasan HOD (e.g., Plant Manager)
    protected User $itStaff;
    protected User $miningStaff;
    protected User $securityUser;
    protected DepartmentApproval $itDeptApproval;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin
        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@sba.co.id',
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 2. IT HOD Approver (e.g. General Manager / Atasan HOD)
        $this->itHodApprover = User::factory()->create([
            'name' => 'Budi GM',
            'email' => 'gm@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'Management',
            'position' => 'Plant General Manager',
            'employee_id' => 'SBA-DIR-001',
        ]);
        UserModule::create(['user_id' => $this->itHodApprover->id, 'module' => 'sik']);

        // 3. IT HOD
        $this->itHod = User::factory()->create([
            'name' => 'Faisal HOD IT',
            'email' => 'hod.it@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Head of IT Department',
            'employee_id' => 'SBA-HOD-002',
        ]);
        UserModule::create(['user_id' => $this->itHod->id, 'module' => 'sik']);

        // 4. IT Staff
        $this->itStaff = User::factory()->create([
            'name' => 'Rian IT Staff',
            'email' => 'rian.it@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Staff Network Engineer',
            'employee_id' => 'SBA-STF-003',
        ]);
        UserModule::create(['user_id' => $this->itStaff->id, 'module' => 'sik']);

        // 5. Mining Staff (different unconfigured department initially)
        $this->miningStaff = User::factory()->create([
            'name' => 'Doni Mining',
            'email' => 'doni.mining@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'Mining & Quarry',
            'position' => 'Operator Heavy Equipment',
            'employee_id' => 'SBA-OPR-004',
        ]);
        UserModule::create(['user_id' => $this->miningStaff->id, 'module' => 'sik']);

        // 6. Security Officer
        $this->securityUser = User::factory()->create([
            'name' => 'Danpos Security',
            'email' => 'security@sba.co.id',
            'role' => 'security',
            'status' => 'active',
            'department' => 'Security & Safety',
            'position' => 'Chief Security Guard',
            'employee_id' => 'SBA-SEC-005',
        ]);

        // 7. Setup Department Approval Configuration for IT & Automation
        $this->itDeptApproval = DepartmentApproval::create([
            'department_name' => 'IT & Automation',
            'hod_user_id' => $this->itHod->id,
            'hod_approver_user_id' => $this->itHodApprover->id,
            'notes' => 'Konfigurasi Approval Departemen IT',
        ]);
    }

    /**
     * Test 1: SIK Number Generation format and uniqueness
     */
    public function test_sik_number_sequential_format()
    {
        $sikService = app(SikService::class);
        $permit1 = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Perbaikan Jaringan Kantor Cabang',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
        ], false);

        $permit2 = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'pribadi',
            'purpose' => 'Ambil Berkas Pribadi',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '13:00',
            'expected_return_time' => '14:00',
        ], false);

        $yearMonth = Carbon::now('Asia/Jakarta')->format('Ym');
        $this->assertEquals("SIK-{$yearMonth}-00001", $permit1->sik_number);
        $this->assertEquals("SIK-{$yearMonth}-00002", $permit2->sik_number);
    }

    /**
     * Test 2: Historical Snapshot Preservation (Department, Position, Employee ID)
     */
    public function test_snapshot_preservation_on_exit_permit()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Meeting Vendor',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
        ], false);

        $this->assertEquals('IT & Automation', $permit->department);
        $this->assertEquals('Staff Network Engineer', $permit->position);
        $this->assertEquals('SBA-STF-003', $permit->employee_id);

        // Modify User in Database
        $this->itStaff->update([
            'department' => 'Corporate Strategic',
            'position' => 'Senior Vice President',
            'employee_id' => 'SBA-NEW-999',
        ]);

        // SIK record must remain unchanged
        $freshPermit = $permit->fresh();
        $this->assertEquals('IT & Automation', $freshPermit->department);
        $this->assertEquals('Staff Network Engineer', $freshPermit->position);
        $this->assertEquals('SBA-STF-003', $freshPermit->employee_id);
    }

    /**
     * Test 3: IT Staff submission sets IT HOD as Approver
     */
    public function test_staff_submission_assigns_department_hod_as_approver()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Pengecekan Server Banda Aceh',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '16:00',
        ], true);

        $this->assertEquals(ExitPermit::STATUS_PENDING_APPROVAL, $permit->status);
        $this->assertEquals($this->itHod->id, $permit->approver_id);
    }

    /**
     * Test 4: IT HOD submission sets Atasan HOD (HOD Approver) as Approver
     */
    public function test_hod_submission_assigns_hod_approver_as_approver()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itHod, [
            'purpose_type' => 'dinas',
            'purpose' => 'Rapat Direksi di Banda Aceh',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '08:00',
            'expected_return_time' => '17:00',
        ], true);

        $this->assertEquals(ExitPermit::STATUS_PENDING_APPROVAL, $permit->status);
        $this->assertEquals($this->itHodApprover->id, $permit->approver_id);
        $this->assertNotEquals($this->itHod->id, $permit->approver_id);
    }

    /**
     * Test 5: HOD Cannot approve their own SIK
     */
    public function test_hod_cannot_approve_their_own_sik()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itHod, [
            'purpose_type' => 'dinas',
            'purpose' => 'Dinas Luar',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '08:00',
            'expected_return_time' => '17:00',
        ], true);

        // Attempt approval by HOD himself
        $this->expectException(ValidationException::class);
        $sikService->approve($permit, $this->itHod, 'Self Approval Attempt');
    }

    /**
     * Test 6: Unconfigured department approver throws clear validation error (No silent admin fallback)
     */
    public function test_unconfigured_approver_throws_clear_validation_error()
    {
        $sikService = app(SikService::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Konfigurasi approval untuk departemen 'Mining & Quarry' belum diatur di sistem.");

        $sikService->createPermit($this->miningStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Cek Alat Berat di Dermaga',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '11:00',
        ], true);
    }

    /**
     * Test 7: Approval generates cryptographically secure QR token and logs history
     */
    public function test_approval_generates_qr_token_and_audit_history()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Maintenance Fiber Optic',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '14:00',
        ], true);

        $this->assertNull($permit->qr_token);

        $sikService->approve($permit, $this->itHod, 'Disetujui. Harap utamakan K3.');

        $fresh = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_APPROVED, $fresh->status);
        $this->assertNotNull($fresh->qr_token);
        $this->assertStringStartsWith('SIK-', $fresh->qr_token);
        $this->assertNotNull($fresh->approved_at);

        // Verify audit history
        $this->assertDatabaseHas('sik_approval_histories', [
            'exit_permit_id' => $permit->id,
            'status' => 'APPROVED',
            'approver_id' => $this->itHod->id,
            'note' => 'Disetujui. Harap utamakan K3.',
        ]);
    }

    /**
     * Test 8: Rejection requires reason and updates status
     */
    public function test_rejection_updates_status_and_audit_history()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'pribadi',
            'purpose' => 'Urusan Pribadi',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '15:00',
        ], true);

        $sikService->reject($permit, $this->itHod, 'Sedang ada maintenance darurat, izin ditolak.');

        $fresh = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_REJECTED, $fresh->status);
        $this->assertEquals('Sedang ada maintenance darurat, izin ditolak.', $fresh->rejection_reason);

        $this->assertDatabaseHas('sik_approval_histories', [
            'exit_permit_id' => $permit->id,
            'status' => 'REJECTED',
            'approver_id' => $this->itHod->id,
        ]);
    }

    /**
     * Test 9: Revoke can only happen on APPROVED status before departure
     */
    public function test_revocation_lifecycle()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Dinas Luar',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
        ], true);

        $sikService->approve($permit, $this->itHod);
        $token = $permit->fresh()->qr_token;

        // Revoke
        $sikService->revoke($permit, $this->itHod, 'Jadwal meeting dibatalkan oleh vendor.');

        $fresh = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_REVOKED, $fresh->status);
        $this->assertNotNull($fresh->revoked_at);
        $this->assertEquals($this->itHod->id, $fresh->revoked_by);

        // Scanning revoked token must fail
        $scanService = app(SikScanService::class);
        $validation = $scanService->validateToken($token);
        $this->assertFalse($validation['valid']);
    }

    /**
     * Test 10: Security Gate Scanner Exit and Return Scans
     */
    public function test_security_gate_scan_exit_and_return()
    {
        $sikService = app(SikService::class);
        $scanService = app(SikScanService::class);

        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Koordinasi Kantor Pelabuhan',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '08:30',
            'expected_return_time' => '16:00',
        ], true);

        $sikService->approve($permit, $this->itHod);
        $token = $permit->fresh()->qr_token;

        // 1. Validate Token before Exit
        $val1 = $scanService->validateToken($token);
        $this->assertTrue($val1['valid']);
        $this->assertEquals('EXIT', $val1['action']);

        // 2. Confirm Exit
        $permitAfterExit = $scanService->confirmExit($permit->id, $this->securityUser, 'Pos Utama', 'Kendaraan dinas Avanza');
        $this->assertEquals(ExitPermit::STATUS_OUTSIDE, $permitAfterExit->status);
        $this->assertNotNull($permitAfterExit->actual_departure_at);
        $this->assertEquals($this->securityUser->id, $permitAfterExit->exit_scanned_by);

        // Verify Scan Log
        $this->assertDatabaseHas('sik_scan_logs', [
            'exit_permit_id' => $permit->id,
            'scan_type' => 'EXIT',
            'scanned_by' => $this->securityUser->id,
            'gate' => 'Pos Utama',
        ]);

        // 3. Validate Token for Return
        $val2 = $scanService->validateToken($token);
        $this->assertTrue($val2['valid']);
        $this->assertEquals('RETURN', $val2['action']);

        // 4. Confirm Return
        $permitAfterReturn = $scanService->confirmReturn($permit->id, $this->securityUser, 'Pos Utama', 'Kembali dalam kondisi baik');
        $this->assertEquals(ExitPermit::STATUS_RETURNED, $permitAfterReturn->status);
        $this->assertNotNull($permitAfterReturn->actual_return_at);
        $this->assertEquals($this->securityUser->id, $permitAfterReturn->return_scanned_by);

        // Verify Scan Log
        $this->assertDatabaseHas('sik_scan_logs', [
            'exit_permit_id' => $permit->id,
            'scan_type' => 'RETURN',
            'scanned_by' => $this->securityUser->id,
        ]);
    }

    /**
     * Test 11: Double Scan Race Condition Protection (Row-Level Locking)
     */
    public function test_double_scan_race_condition_prevention()
    {
        $sikService = app(SikService::class);
        $scanService = app(SikScanService::class);

        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Tugas Lapangan',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '13:00',
        ], true);
        $sikService->approve($permit, $this->itHod);

        // First scan succeeds
        $scanService->confirmExit($permit->id, $this->securityUser);

        // Immediate duplicate scan attempt must throw exception
        $this->expectException(ValidationException::class);
        $scanService->confirmExit($permit->id, $this->securityUser);
    }

    /**
     * Test 12: Dynamic OVERDUE Calculation based on Asia/Jakarta timezone
     */
    public function test_dynamic_overdue_status_calculation()
    {
        $permit = ExitPermit::create([
            'sik_number' => 'SIK-202609-99999',
            'user_id' => $this->itStaff->id,
            'department' => 'IT & Automation',
            'position' => 'Staff',
            'employee_id' => 'SBA-001',
            'purpose_type' => 'dinas',
            'purpose' => 'Overdue Test',
            'permit_date' => Carbon::now('Asia/Jakarta')->subDays(1)->toDateString(), // yesterday
            'expected_departure_time' => '08:00',
            'expected_return_time' => '10:00',
            'status' => ExitPermit::STATUS_OUTSIDE,
            'actual_departure_at' => Carbon::now('Asia/Jakarta')->subDays(1)->setTime(8, 5),
            'qr_token' => 'SIK-OVERDUE-TEST-TOKEN',
        ]);

        $this->assertEquals('outside', $permit->status);
        $this->assertEquals('overdue', $permit->effective_status);
        $this->assertTrue($permit->isOverdue());
    }

    /**
     * Test 13: Manual Close does NOT touch actual_return_at
     */
    public function test_closed_manually_does_not_fill_actual_return_at()
    {
        $sikService = app(SikService::class);
        $scanService = app(SikScanService::class);

        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Inspeksi Luar',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '08:00',
            'expected_return_time' => '12:00',
        ], true);
        $sikService->approve($permit, $this->itHod);
        $scanService->confirmExit($permit->id, $this->securityUser);

        // Emergency manual close
        $sikService->closeManually($permit, $this->itHod, 'Karyawan mengalami kecelakaan ringan dan langsung dirawat di RS Banda Aceh.');

        $fresh = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_CLOSED_MANUALLY, $fresh->status);
        $this->assertNull($fresh->actual_return_at); // MUST NOT BE TOUCHED
        $this->assertNotNull($fresh->closed_at);
        $this->assertEquals($this->itHod->id, $fresh->closed_by);
    }

    /**
     * Test 14: Server-Side IDOR & Policy Authorization
     */
    public function test_user_cannot_view_or_approve_other_departments_permits()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Server Maintenance',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
        ], true);

        // Mining staff (different user/dept) tries to access IT staff permit
        $response = $this->actingAs($this->miningStaff)->get(route('sik.show', $permit));
        $response->assertStatus(403);

        // Mining staff tries to approve IT staff permit
        $responseApprove = $this->actingAs($this->miningStaff)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'Unauthorized Approval Attempt',
        ]);
        $responseApprove->assertStatus(403);
    }

    /**
     * Test 15: Security Scanner Endpoint Access restricted strictly to Security role
     */
    public function test_security_scanner_access_control()
    {
        // Regular user blocked from scanner
        $resUser = $this->actingAs($this->itStaff)->get(route('sik.scanner'));
        $resUser->assertStatus(403);

        // System Admin without security role is blocked from scanner
        $resAdmin = $this->actingAs($this->admin)->get(route('sik.scanner'));
        $resAdmin->assertStatus(403);

        // Security officer allowed
        $resSec = $this->actingAs($this->securityUser)->get(route('sik.scanner'));
        $resSec->assertStatus(200);
        $resSec->assertSee('Cek Validasi');
        $resSec->assertSee('btnCekToken');
        $resSec->assertSee('local_police');
    }

    /**
     * Test 20: System Admin without complete employee identity cannot apply for SIK
     */
    public function test_pure_system_admin_cannot_apply_for_sik_and_redirects_to_admin_index()
    {
        // Pure admin (no employee_id, no department, no position)
        $this->assertFalse($this->admin->isEmployee());

        // 1. Create page is blocked
        $resCreate = $this->actingAs($this->admin)->get(route('sik.create'));
        $resCreate->assertStatus(403);

        // 2. Store POST is blocked
        $resStore = $this->actingAs($this->admin)->post(route('sik.store'), [
            'purpose_type' => 'dinas',
            'purpose' => 'Meeting External',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
            'action' => 'draft',
        ]);
        $resStore->assertStatus(403);

        // 3. Visiting SIK Saya redirects pure admin to administrative index
        $resIndex = $this->actingAs($this->admin)->get(route('sik.index'));
        $resIndex->assertRedirect(route('admin.sik.index'));

        // 4. Pure Admin who is not HOD cannot visit Approvals queue
        $resApprovals = $this->actingAs($this->admin)->get(route('sik.approvals.index'));
        $resApprovals->assertStatus(403);

        // 5. Admin CAN access administrative pages
        $resAdminDash = $this->actingAs($this->admin)->get(route('admin.sik.dashboard'));
        $resAdminDash->assertStatus(200);
        $resAdminDash->assertSee('Surat Izin Keluar (SIK)');
        $resAdminDash->assertDontSee('Reservasi Ruang Meeting');

        $resAdminAll = $this->actingAs($this->admin)->get(route('admin.sik.index'));
        $resAdminAll->assertStatus(200);
        $resAdminAll->assertSee('Surat Izin Keluar (SIK)');
        $resAdminAll->assertDontSee('Reservasi Ruang Meeting');

        $resAdminAudit = $this->actingAs($this->admin)->get(route('admin.sik.audit-logs'));
        $resAdminAudit->assertStatus(200);

        // 6. HOD Department History header title verification
        $resHodHistory = $this->actingAs($this->itHod)->get(route('sik.history.index'));
        $resHodHistory->assertStatus(200);
        $resHodHistory->assertSee('Surat Izin Keluar (SIK)');
        $resHodHistory->assertDontSee('Reservasi Ruang Meeting');
    }

    /**
     * Test 21: Admin who IS an employee can apply for SIK and follows standard department workflow
     */
    public function test_admin_employee_can_apply_for_sik_and_cannot_self_approve()
    {
        // Create Admin who is also an employee in IT Department
        $adminEmployee = User::factory()->create([
            'name' => 'Admin Staff IT',
            'email' => 'admin.staff@sba.co.id',
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Senior System Administrator',
            'employee_id' => 'SBA-ADM-007',
        ]);
        UserModule::create(['user_id' => $adminEmployee->id, 'module' => 'sik']);

        $this->assertTrue($adminEmployee->isEmployee());

        // 1. Can access create page
        $resCreate = $this->actingAs($adminEmployee)->get(route('sik.create'));
        $resCreate->assertStatus(200);

        // 2. Can submit SIK
        $resStore = $this->actingAs($adminEmployee)->post(route('sik.store'), [
            'purpose_type' => 'dinas',
            'purpose' => 'Perbaikan Server DC',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '15:00',
            'action' => 'submit',
        ]);
        $resStore->assertRedirect();

        $permit = ExitPermit::where('user_id', $adminEmployee->id)->first();
        $this->assertNotNull($permit);
        $this->assertEquals(ExitPermit::STATUS_PENDING_APPROVAL, $permit->status);
        $this->assertEquals($this->itHod->id, $permit->approver_id); // Assigned to IT HOD

        // 3. Admin Employee CANNOT approve their own SIK
        $resApprove = $this->actingAs($adminEmployee)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'Self-approval try',
        ]);
        $resApprove->assertStatus(403);
    }

    /**
     * Test 22: Navigation Service builds segregated menus for Pure Admin vs Admin Employee vs Security vs User
     */
    public function test_navigation_service_menu_segregation()
    {
        $navService = app(\App\Services\NavigationService::class);

        // 1. Pure Admin
        $pureAdminMenu = $navService->getMenuForUser($this->admin);
        $sikGroup = collect($pureAdminMenu)->firstWhere('id', 'sik_menu');
        $this->assertNotNull($sikGroup);
        $submenuTitles = collect($sikGroup['submenu'])->pluck('title')->toArray();

        // Must NOT have personal applicant/scanner items
        $this->assertNotContains('Ajukan SIK', $submenuTitles);
        $this->assertNotContains('SIK Saya', $submenuTitles);
        $this->assertNotContains('Scanner SIK', $submenuTitles);
        $this->assertNotContains('Persetujuan SIK', $submenuTitles);

        // MUST have administrative items
        $this->assertContains('Dashboard SIK', $submenuTitles);
        $this->assertContains('Semua SIK', $submenuTitles);
        $this->assertContains('Monitoring', $submenuTitles);
        $this->assertContains('Riwayat SIK', $submenuTitles);
        $this->assertContains('Riwayat Scan', $submenuTitles);
        $this->assertContains('Konfigurasi Approval', $submenuTitles);
        $this->assertContains('Audit Log', $submenuTitles);

        // Settings group must NOT have Approval Departemen (redundancy removed)
        $settingsGroup = collect($pureAdminMenu)->firstWhere('id', 'settings_menu');
        $this->assertNotNull($settingsGroup);
        $settingsTitles = collect($settingsGroup['submenu'])->pluck('title')->toArray();
        $this->assertContains('Pengguna', $settingsTitles);
        $this->assertContains('Cost Center', $settingsTitles);
        $this->assertContains('SMTP Settings', $settingsTitles);
        $this->assertNotContains('Approval Departemen', $settingsTitles);

        // 2. Admin Employee
        $adminEmployee = User::factory()->create([
            'name' => 'Admin Staff GA',
            'email' => 'admin.ga@sba.co.id',
            'role' => 'admin',
            'status' => 'active',
            'department' => 'General Affairs',
            'position' => 'Staff GA',
            'employee_id' => 'SBA-GA-001',
        ]);
        $adminEmpMenu = $navService->getMenuForUser($adminEmployee);
        $adminEmpSikGroup = collect($adminEmpMenu)->firstWhere('id', 'sik_menu');
        $adminEmpTitles = collect($adminEmpSikGroup['submenu'])->pluck('title')->toArray();

        $this->assertContains('Ajukan SIK', $adminEmpTitles);
        $this->assertContains('SIK Saya', $adminEmpTitles);
        $this->assertContains('Dashboard SIK', $adminEmpTitles);
        $this->assertContains('Semua SIK', $adminEmpTitles);

        // 3. Security
        $secMenu = $navService->getMenuForUser($this->securityUser);
        $secSikGroup = collect($secMenu)->firstWhere('id', 'sik_menu');
        $secTitles = collect($secSikGroup['submenu'])->pluck('title')->toArray();

        $this->assertContains('Dashboard', $secTitles);
        $this->assertContains('Scanner SIK', $secTitles);
        $this->assertContains('Riwayat SIK', $secTitles);
        $this->assertNotContains('Ajukan SIK', $secTitles);
        $this->assertNotContains('Persetujuan SIK', $secTitles);

        // 4. Regular User Employee
        $userMenu = $navService->getMenuForUser($this->itStaff);
        $userSikGroup = collect($userMenu)->firstWhere('id', 'sik_menu');
        $userTitles = collect($userSikGroup['submenu'])->pluck('title')->toArray();

        $this->assertContains('Ajukan SIK', $userTitles);
        $this->assertContains('SIK Saya', $userTitles);
        $this->assertNotContains('Scanner SIK', $userTitles);
        $this->assertNotContains('Dashboard SIK', $userTitles);
        $this->assertNotContains('Konfigurasi Approval', $userTitles);
    }

    /**
     * Test 16: HTTP SIK Store and Cancel workflow by Applicant
     */
    public function test_applicant_http_crud_workflow()
    {
        // 1. Submit Draft via HTTP
        $responseStore = $this->actingAs($this->itStaff)->post(route('sik.store'), [
            'purpose_type' => 'dinas',
            'purpose' => 'Koordinasi Kantor Pelabuhan Malahayati',
            'purpose_detail' => 'Rincian meeting teknis',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '15:00',
            'vehicle_type' => 'company_vehicle',
            'vehicle_details' => 'BL 1010 AB',
            'action' => 'draft',
        ]);

        $responseStore->assertRedirect();
        $this->assertDatabaseHas('exit_permits', [
            'user_id' => $this->itStaff->id,
            'purpose' => 'Koordinasi Kantor Pelabuhan Malahayati',
            'status' => 'draft',
        ]);

        $permit = ExitPermit::where('user_id', $this->itStaff->id)->first();

        // 2. View Show
        $resShow = $this->actingAs($this->itStaff)->get(route('sik.show', $permit));
        $resShow->assertStatus(200);

        // 3. Edit Draft
        $resEdit = $this->actingAs($this->itStaff)->get(route('sik.edit', $permit));
        $resEdit->assertStatus(200);

        // 4. Update Draft & Direct Submit
        $resUpdate = $this->actingAs($this->itStaff)->put(route('sik.update', $permit), [
            'purpose_type' => 'dinas',
            'purpose' => 'Koordinasi Kantor Pelabuhan Malahayati Updated',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '16:00',
            'action' => 'submit',
        ]);
        $resUpdate->assertRedirect();

        $fresh = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_PENDING_APPROVAL, $fresh->status);
        $this->assertEquals($this->itHod->id, $fresh->approver_id);

        // 5. Cancel Pending Permit by Applicant
        $resCancel = $this->actingAs($this->itStaff)->post(route('sik.cancel', $permit));
        $resCancel->assertRedirect(route('sik.index'));
        $this->assertDatabaseMissing('exit_permits', ['id' => $permit->id]);
    }

    /**
     * Test 17: HTTP Approver Decisions (Approve, Reject, Revoke, Close Manually)
     */
    public function test_approver_http_decisions_workflow()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Uji Jaringan',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
        ], true);

        // 1. Approvals Index & Show
        $resApprIndex = $this->actingAs($this->itHod)->get(route('sik.approvals.index'));
        $resApprIndex->assertStatus(200);

        $resApprShow = $this->actingAs($this->itHod)->get(route('sik.approvals.show', $permit));
        $resApprShow->assertStatus(200);

        // 2. Approve via HTTP
        $resApprove = $this->actingAs($this->itHod)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'Disetujui dari web approval.',
        ]);
        $resApprove->assertRedirect(route('sik.approvals.index'));
        $this->assertEquals(ExitPermit::STATUS_APPROVED, $permit->fresh()->status);

        // 3. Revoke via HTTP before departure
        $resRevoke = $this->actingAs($this->itHod)->post(route('sik.approvals.revoke', $permit), [
            'revoke_reason' => 'Dibatalkan pimpinan.',
        ]);
        $resRevoke->assertRedirect();
        $this->assertEquals(ExitPermit::STATUS_REVOKED, $permit->fresh()->status);
    }

    /**
     * Test 18: Security Scanner AJAX and Gate Confirmation
     */
    public function test_security_scanner_ajax_and_gate_confirmation()
    {
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Pengantaran Server',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '08:00',
            'expected_return_time' => '14:00',
        ], true);
        $sikService->approve($permit, $this->itHod);
        $token = $permit->fresh()->qr_token;

        // 1. AJAX Scan Token
        $scanRes = $this->actingAs($this->securityUser)->postJson(route('sik.scanner.scan'), [
            'qr_token' => $token,
        ]);
        $scanRes->assertStatus(200);
        $scanRes->assertJson([
            'valid' => true,
            'action' => 'EXIT',
        ]);

        // 2. AJAX Confirm Exit
        $exitRes = $this->actingAs($this->securityUser)->postJson(route('sik.scanner.exit', $permit), [
            'gate' => 'Pos Gerbang Timur',
            'notes' => 'Mobil Dinas',
        ]);
        $exitRes->assertStatus(200);
        $exitRes->assertJson(['success' => true]);
        $this->assertEquals(ExitPermit::STATUS_OUTSIDE, $permit->fresh()->status);

        // 3. AJAX Confirm Return
        $returnRes = $this->actingAs($this->securityUser)->postJson(route('sik.scanner.return', $permit), [
            'gate' => 'Pos Gerbang Timur',
            'notes' => 'Kembali aman',
        ]);
        $returnRes->assertStatus(200);
        $returnRes->assertJson(['success' => true]);
        $this->assertEquals(ExitPermit::STATUS_RETURNED, $permit->fresh()->status);

        // 4. View Scan Logs
        $logsRes = $this->actingAs($this->securityUser)->get(route('sik.scan-logs'));
        $logsRes->assertStatus(200);
    }

    /**
     * Test 19: Admin Department Approvals Configuration CRUD
     */
    public function test_admin_department_approvals_crud()
    {
        // 1. View Index
        $resIndex = $this->actingAs($this->admin)->get(route('admin.settings.department-approvals.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Surat Izin Keluar (SIK)');
        $resIndex->assertDontSee('Pengaturan Sistem');

        // 2. Store Config
        $resStore = $this->actingAs($this->admin)->post(route('admin.settings.department-approvals.store'), [
            'department_name' => 'General Affairs',
            'hod_user_id' => $this->itHod->id,
            'hod_approver_user_id' => $this->itHodApprover->id,
            'notes' => 'Delegasi GA ke HOD IT',
        ]);
        $resStore->assertRedirect(route('admin.settings.department-approvals.index'));

        $this->assertDatabaseHas('department_approvals', [
            'department_name' => 'General Affairs',
            'hod_user_id' => $this->itHod->id,
            'hod_approver_user_id' => $this->itHodApprover->id,
        ]);

        $gaConfig = DepartmentApproval::where('department_name', 'General Affairs')->first();

        // 3. Update Config
        $resUpdate = $this->actingAs($this->admin)->put(route('admin.settings.department-approvals.update', $gaConfig), [
            'department_name' => 'General Affairs',
            'hod_user_id' => $this->itStaff->id,
            'hod_approver_user_id' => $this->itHodApprover->id,
            'notes' => 'Updated notes',
        ]);
        $resUpdate->assertRedirect(route('admin.settings.department-approvals.index'));
        $this->assertEquals($this->itStaff->id, $gaConfig->fresh()->hod_user_id);

        // 4. Destroy Config
        $resDestroy = $this->actingAs($this->admin)->delete(route('admin.settings.department-approvals.destroy', $gaConfig));
        $resDestroy->assertRedirect(route('admin.settings.department-approvals.index'));
        $this->assertDatabaseMissing('department_approvals', ['id' => $gaConfig->id]);
    }

    /**
     * Test 20: Replacement Approver Configuration, Selection & Approval Workflow
     */
    public function test_replacement_approver_configuration_and_approval_workflow()
    {
        // 1. Create a replacement approver user
        $replacementUser = User::factory()->create([
            'name' => 'Citra Supervisor IT',
            'email' => 'citra.spv@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Supervisor Infrastructure',
            'employee_id' => 'SBA-SPV-010',
        ]);
        UserModule::create(['user_id' => $replacementUser->id, 'module' => 'sik']);

        // Configure replacement approver in Approval Setting
        $deptService = app(DepartmentApprovalService::class);
        $deptService->saveDepartmentConfig(
            'IT & Automation',
            $this->itHod->id,
            $this->itHodApprover->id,
            'Catatan IT',
            [$replacementUser->id]
        );

        $this->assertTrue($this->itDeptApproval->fresh()->replacementApprovers->contains($replacementUser));

        // 2. Staff creates SIK choosing Replacement Approver
        $sikService = app(SikService::class);
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Meeting Vendor di Luar Pabrik',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '14:00',
            'approver_type' => 'replacement',
            'replacement_approver_id' => $replacementUser->id,
        ], true);

        // 3. Verify snapshot on ExitPermit
        $this->assertEquals($replacementUser->id, $permit->approver_id);
        $this->assertTrue($permit->is_replacement_approver);
        $this->assertEquals(ExitPermit::STATUS_PENDING_APPROVAL, $permit->status);

        // 4. Verify Approval Menu Visibility for replacement user
        $navService = app(\App\Services\NavigationService::class);
        $repMenu = $navService->getMenuForUser($replacementUser);
        $sikGroup = collect($repMenu)->firstWhere('id', 'sik_menu');
        $repTitles = collect($sikGroup['submenu'])->pluck('title')->toArray();
        $this->assertContains('Persetujuan SIK', $repTitles);

        // 5. Replacement approver views the queue and approves
        $resQueue = $this->actingAs($replacementUser)->get(route('sik.approvals.index'));
        $resQueue->assertStatus(200);
        $resQueue->assertSee($permit->sik_number);

        // HOD does not see it in their queue because it was assigned to replacement approver!
        $resHodQueue = $this->actingAs($this->itHod)->get(route('sik.approvals.index'));
        $resHodQueue->assertStatus(200);
        $resHodQueue->assertDontSee($permit->sik_number);

        // 6. Replacement approver approves the SIK
        $resApprove = $this->actingAs($replacementUser)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'Disetujui oleh Supervisor (Approver Pengganti)',
        ]);
        $resApprove->assertRedirect(route('sik.approvals.index'));

        $freshPermit = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_APPROVED, $freshPermit->status);

        // 7. Verify SikApprovalHistory has NOT NULL approver_type = 'replacement'
        $history = SikApprovalHistory::where('exit_permit_id', $permit->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals('replacement', $history->approver_type);
        $this->assertEquals($replacementUser->id, $history->approver_id);
        $this->assertEquals('APPROVED', $history->status);
    }

    /**
     * Test 21: Unauthorized Replacement Approver is Rejected
     */
    public function test_unauthorized_replacement_approver_is_rejected()
    {
        $unauthorizedUser = User::factory()->create([
            'name' => 'Random User',
            'email' => 'random@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Staff',
            'employee_id' => 'SBA-STF-999',
        ]);

        $this->expectException(ValidationException::class);

        $sikService = app(SikService::class);
        $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Uji Coba Arbitrary Approver',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '12:00',
            'approver_type' => 'replacement',
            'replacement_approver_id' => $unauthorizedUser->id,
        ], true);
    }

    /**
     * Test 22: Self-Approval is Strictly Prevented
     */
    public function test_self_approval_is_strictly_prevented()
    {
        // 1. Applicant tries to select self as replacement approver
        try {
            $sikService = app(SikService::class);
            $sikService->createPermit($this->itStaff, [
                'purpose_type' => 'dinas',
                'purpose' => 'Self Approval Attempt',
                'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
                'expected_departure_time' => '10:00',
                'expected_return_time' => '12:00',
                'approver_type' => 'replacement',
                'replacement_approver_id' => $this->itStaff->id,
            ], true);
            $this->fail('Expected self-approval selection to throw ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('replacement_approver_id', $e->errors());
        }

        // 2. If somehow an SIK has approver_id == user_id, approve action is blocked
        $permit = ExitPermit::create([
            'sik_number' => 'SIK-TEST-SELF',
            'user_id' => $this->itStaff->id,
            'department' => 'IT & Automation',
            'position' => 'Staff',
            'employee_id' => 'SBA-001',
            'purpose_type' => 'dinas',
            'purpose' => 'Test Self Approval',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '11:00',
            'status' => ExitPermit::STATUS_PENDING_APPROVAL,
            'approver_id' => $this->itStaff->id,
        ]);

        $res = $this->actingAs($this->itStaff)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'I approve myself',
        ]);
        $res->assertStatus(403);
    }

    /**
     * Test 23: Approver Snapshot Immutability & Authority
     */
    public function test_approver_snapshot_immutability_and_authority()
    {
        $sikService = app(SikService::class);

        // Staff submits SIK with Faisal as HOD (Day 1)
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Tugas Luar Day 1',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => '09:00',
            'expected_return_time' => '12:00',
            'approver_type' => 'default',
        ], true);

        $this->assertEquals($this->itHod->id, $permit->approver_id);

        // Day 2: Admin changes IT HOD to Hendra
        $newHod = User::factory()->create([
            'name' => 'Hendra New HOD',
            'email' => 'hendra.hod@sba.co.id',
            'role' => 'user',
            'status' => 'active',
            'department' => 'IT & Automation',
            'position' => 'Head of IT',
            'employee_id' => 'SBA-HOD-NEW',
        ]);
        UserModule::create(['user_id' => $newHod->id, 'module' => 'sik']);

        $deptService = app(DepartmentApprovalService::class);
        $deptService->saveDepartmentConfig(
            'IT & Automation',
            $newHod->id,
            $this->itHodApprover->id,
            'Perubahan HOD Baru'
        );

        // 1. Existing SIK approver_id MUST still be Faisal
        $this->assertEquals($this->itHod->id, $permit->fresh()->approver_id);

        // 2. Hendra CANNOT approve Faisal's old SIK (blocked by policy and queue)
        $resHendra = $this->actingAs($newHod)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'I am the new HOD',
        ]);
        $resHendra->assertStatus(403);

        // 3. Faisal CAN approve the SIK because he is the authoritative snapshotted approver
        $resFaisal = $this->actingAs($this->itHod)->post(route('sik.approvals.approve', $permit), [
            'approval_note' => 'Approved by original HOD',
        ]);
        $resFaisal->assertRedirect(route('sik.approvals.index'));
        $this->assertEquals(ExitPermit::STATUS_APPROVED, $permit->fresh()->status);
    }

    /**
     * Test 24: Security Dashboard, 6 Metric Cards, and Operational Table Live Timers
     */
    public function test_security_dashboard_and_operational_timer_data()
    {
        $sikService = app(SikService::class);
        $scanService = app(SikScanService::class);

        // 1. Create and approve permit
        $permit = $sikService->createPermit($this->itStaff, [
            'purpose_type' => 'dinas',
            'purpose' => 'Instalasi Server Malahayati',
            'permit_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'expected_departure_time' => Carbon::now('Asia/Jakarta')->subMinutes(30)->format('H:i'),
            'expected_return_time' => Carbon::now('Asia/Jakarta')->addMinutes(60)->format('H:i'),
        ], true);
        $sikService->approve($permit, $this->itHod);

        // 2. Confirm Exit
        $scanService->confirmExit($permit->id, $this->securityUser, 'Pos Utama');
        $this->assertEquals(ExitPermit::STATUS_OUTSIDE, $permit->fresh()->status);
        $this->assertNotNull($permit->fresh()->actual_departure_at);

        // 3. Visit Security Dashboard
        $resDash = $this->actingAs($this->securityUser)->get(route('sik.security.dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('Dashboard Operasional Security');
        $resDash->assertSee('Karyawan Sedang di Luar Pabrik');
        $resDash->assertSee($this->itStaff->name);
        $resDash->assertSee('Total SIK');
        $resDash->assertSee('Menunggu');
        $resDash->assertSee('Di Luar Pabrik');
        $resDash->assertSee('Terlambat');
        $resDash->assertSee('Kembali Hari Ini');
        $resDash->assertSee('Ditolak / Arsip');

        // 4. Test duration helpers
        $fresh = $permit->fresh();
        $fresh->actual_departure_at = Carbon::now('Asia/Jakarta')->subMinutes(5);
        $fresh->save();

        $fresh = $fresh->fresh();
        $this->assertGreaterThan(0, $fresh->actual_duration_seconds);
        $this->assertNotEmpty($fresh->actual_duration_formatted);
        $this->assertFalse($fresh->is_late);

        // 5. Confirm Return
        $scanService->confirmReturn($permit->id, $this->securityUser, 'Pos Utama');
        $returned = $permit->fresh();
        $this->assertEquals(ExitPermit::STATUS_RETURNED, $returned->status);
        $this->assertNotNull($returned->actual_return_at);
        $this->assertGreaterThanOrEqual(0, $returned->actual_duration_seconds);
    }

    /**
     * Test 28: GA SIK Module menu and route access matches Admin except Konfigurasi Approval is strictly Admin-only.
     */
    public function test_ga_sik_module_menu_and_route_access_matches_admin_except_approval_config(): void
    {
        $gaUser = User::factory()->create([
            'name' => 'GA Officer',
            'email' => 'ga.officer@sba.co.id',
            'role' => 'ga',
            'status' => 'active',
        ]);
        UserModule::create(['user_id' => $gaUser->id, 'module' => 'sik']);

        $navService = app(\App\Services\NavigationService::class);

        // 1. Navigation Menu assertions
        $gaMenu = $navService->getMenuForUser($gaUser);
        $sikGroup = collect($gaMenu)->firstWhere('id', 'sik_menu');
        $this->assertNotNull($sikGroup, 'GA must have SIK menu when module access granted.');
        $submenuTitles = collect($sikGroup['submenu'])->pluck('title')->toArray();

        // GA MUST have the administrative items matching Admin
        $this->assertContains('Dashboard SIK', $submenuTitles);
        $this->assertContains('Semua SIK', $submenuTitles);
        $this->assertContains('Monitoring', $submenuTitles);
        $this->assertContains('Riwayat SIK', $submenuTitles);
        $this->assertContains('Riwayat Scan', $submenuTitles);
        $this->assertContains('Audit Log', $submenuTitles);

        // GA MUST NOT have Konfigurasi Approval
        $this->assertNotContains('Konfigurasi Approval', $submenuTitles, 'Konfigurasi Approval must not be visible to GA.');

        // 2. GA CAN access administrative SIK pages
        $resDashboard = $this->actingAs($gaUser)->get(route('admin.sik.dashboard'));
        $resDashboard->assertStatus(200);
        $resDashboard->assertDontSee(route('admin.settings.department-approvals.index'));

        $resIndex = $this->actingAs($gaUser)->get(route('admin.sik.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertDontSee(route('admin.settings.department-approvals.index'));

        $resAudit = $this->actingAs($gaUser)->get(route('admin.sik.audit-logs'));
        $resAudit->assertStatus(200);

        $resScanLogs = $this->actingAs($gaUser)->get(route('sik.scan-logs'));
        $resScanLogs->assertStatus(200);

        $resDept = $this->actingAs($gaUser)->get(route('sik.department.index'));
        $resDept->assertStatus(200);

        $resHistory = $this->actingAs($gaUser)->get(route('sik.history.index'));
        $resHistory->assertStatus(200);

        // 3. GA CANNOT access Department Approval configuration routes (Strictly Admin only)
        $resApprovalIndex = $this->actingAs($gaUser)->get(route('admin.settings.department-approvals.index'));
        $resApprovalIndex->assertStatus(403);

        $resApprovalStore = $this->actingAs($gaUser)->post(route('admin.settings.department-approvals.store'), [
            'department_name' => 'Unauthorized Dept',
        ]);
        $resApprovalStore->assertStatus(403);

        // 4. GA without SIK module is denied from administrative SIK pages
        $gaWithoutSik = User::factory()->create([
            'name' => 'GA Other',
            'email' => 'ga.other@sba.co.id',
            'role' => 'ga',
            'status' => 'active',
        ]);
        UserModule::create(['user_id' => $gaWithoutSik->id, 'module' => 'vehicle']);

        $resDeniedDash = $this->actingAs($gaWithoutSik)->get(route('admin.sik.dashboard'));
        $resDeniedDash->assertStatus(403);

        // 5. Regular User is denied from administrative SIK pages
        $regularUser = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);
        UserModule::create(['user_id' => $regularUser->id, 'module' => 'sik']);

        $resUserDenied = $this->actingAs($regularUser)->get(route('admin.sik.dashboard'));
        $resUserDenied->assertStatus(403);
    }
}
