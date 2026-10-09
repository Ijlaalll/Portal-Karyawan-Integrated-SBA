<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $this->user->modules()->create(['module' => 'meeting_room']);
    }

    /**
     * Test Admin is authorized to access Users Management and view creation form.
     */
    public function test_admin_can_access_user_management_views(): void
    {
        // View index
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('+ Tambah Pengguna');

        // View create form
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Pengguna');
        $response->assertSee('Nama');
        $response->assertSee('Email');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Konfirmasi Kata Sandi');
        $response->assertSee('Peran');
        $response->assertSee('Akses Manajemen');
    }

    /**
     * Test Admin can create a new user with valid data.
     */
    public function test_admin_can_create_user_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'New Employee',
            'email' => 'employee@example.com',
            'password' => 'securepassword123',
            'password_confirmation' => 'securepassword123',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dibuat.');

        // Verify in database
        $this->assertDatabaseHas('users', [
            'name' => 'New Employee',
            'email' => 'employee@example.com',
            'role' => 'user',
        ]);

        $createdUser = User::where('email', 'employee@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasModuleAccess('meeting_room'));
        $this->assertFalse($createdUser->hasModuleAccess('toilet_inspection'));
        
        // Verify password is not saved in plaintext
        $this->assertNotEquals('securepassword123', $createdUser->password);
        
        // Verify password is hashed correctly
        $this->assertTrue(Hash::check('securepassword123', $createdUser->password));
    }

    /**
     * Test Admin can create a new SPV with Toilet Inspection module access.
     */
    public function test_admin_can_create_spv_with_toilet_inspection(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Spv Inspection',
            'email' => 'spv@example.com',
            'password' => 'securepassword123',
            'password_confirmation' => 'securepassword123',
            'role' => 'spv',
            'modules' => ['toilet_inspection'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dibuat.');

        $this->assertDatabaseHas('users', [
            'name' => 'Spv Inspection',
            'email' => 'spv@example.com',
            'role' => 'spv',
        ]);

        $user = User::where('email', 'spv@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasModuleAccess('toilet_inspection'));
        $this->assertFalse($user->hasModuleAccess('meeting_room'));
    }

    /**
     * Test validation rules for user creation.
     */
    public function test_user_creation_validation_rules(): void
    {
        // 1. Empty name
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);
        $response->assertSessionHasErrors('name');

        // 2. Invalid email format
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);
        $response->assertSessionHasErrors('email');

        // 3. Duplicate email
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Duplicate Email User',
            'email' => 'user@example.com', // Already taken by $this->user
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors(['email' => 'Email ini sudah terdaftar.']);

        // 4. Password mismatch
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Mismatch Pass User',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);
        $response->assertSessionHasErrors('password');

        // 5. Invalid role
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Invalid Role User',
            'email' => 'invalidrole@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'superadmin',
            'modules' => ['meeting_room'],
        ]);
        $response->assertSessionHasErrors('role');

        // 6. Missing modules for non-admin
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'No Modules User',
            'email' => 'nomodules@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => [],
        ]);
        $response->assertSessionHasErrors('modules');

        // 7. Invalid modules
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Invalid Modules User',
            'email' => 'invalidmodules@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['unknown_module'],
        ]);
        $response->assertSessionHasErrors('modules.0');
    }

    /**
     * Test Login redirects user directly to their assigned module's dashboard.
     */
    public function test_login_redirects_to_assigned_module_dashboard(): void
    {
        // Meeting room user
        $userMR = User::create([
            'name' => 'Meeting Room User',
            'email' => 'mr_user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $userMR->modules()->create(['module' => 'meeting_room']);

        $response = $this->post('/login', [
            'email' => 'mr_user@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('dashboard'));

        // Toilet inspection user
        $userTI = User::create([
            'name' => 'Toilet Inspection User',
            'email' => 'ti_user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $userTI->modules()->create(['module' => 'toilet_inspection']);

        $response = $this->post('/login', [
            'email' => 'ti_user@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('toilet_inspection.dashboard'));
    }

    /**
     * Test module route authorization protection.
     */
    public function test_module_route_authorization(): void
    {
        $userMR = User::create([
            'name' => 'MR User',
            'email' => 'mr@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $userMR->modules()->create(['module' => 'meeting_room']);

        $userTI = User::create([
            'name' => 'TI User',
            'email' => 'ti@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $userTI->modules()->create(['module' => 'toilet_inspection']);

        // Meeting room user accessing Toilet Inspection -> BLOCKED (403)
        $this->actingAs($userMR)->get(route('toilet_inspection.dashboard'))->assertStatus(403);

        // Toilet inspection user accessing Meeting Room -> BLOCKED (403)
        $this->actingAs($userTI)->get(route('dashboard'))->assertStatus(403);
        $this->actingAs($userTI)->get(route('calendar'))->assertStatus(403);
        $this->actingAs($userTI)->get(route('rooms.search'))->assertStatus(403);

        // Admin has access to both
        $this->actingAs($this->admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('toilet_inspection.dashboard'))->assertStatus(200);
    }

    /**
     * Test Guest and Regular User are denied access to User Management.
     */
    public function test_non_admin_cannot_access_user_management(): void
    {
        // --- Guest Access Denied ---
        // Access index
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        // Access create form
        $this->get(route('admin.users.create'))->assertRedirect(route('login'));
        // Access store
        $this->post(route('admin.users.store'), [])->assertRedirect(route('login'));

        // --- Regular User Access Denied (403) ---
        // Access index
        $this->actingAs($this->user)->get(route('admin.users.index'))->assertStatus(403);
        // Access create form
        $this->actingAs($this->user)->get(route('admin.users.create'))->assertStatus(403);
        // Access store
        $this->actingAs($this->user)->post(route('admin.users.store'), [
            'name' => 'Hacked User',
            'email' => 'hack@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['meeting_room'],
        ])->assertStatus(403);

        // Verify database is unaffected
        $this->assertDatabaseMissing('users', ['email' => 'hack@example.com']);
    }

    /**
     * Test search by name and email in User Management.
     */
    public function test_user_management_search_by_name_and_email(): void
    {
        // Search by name "Regular" (should find Regular User)
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Regular']));
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->contains($this->user));
        $this->assertFalse($users->contains($this->admin));

        // Search by email "admin@example.com" (should find Admin User)
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'admin@example.com']));
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->contains($this->admin));
        $this->assertFalse($users->contains($this->user));
    }

    /**
     * Test role and management filters in User Management.
     */
    public function test_user_management_filters(): void
    {
        // Create an SPV user for Toilet Inspection
        $spv = User::create([
            'name' => 'Supervisor Toilet',
            'email' => 'spv_toilet@example.com',
            'password' => Hash::make('password'),
            'role' => 'spv',
        ]);
        $spv->modules()->create(['module' => 'toilet_inspection']);

        // 1. Filter by Role: SPV
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'spv']));
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->contains($spv));
        $this->assertFalse($users->contains($this->user));
        $this->assertFalse($users->contains($this->admin));

        // 2. Filter by Management: Toilet Inspection
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['management' => 'toilet_inspection']));
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->contains($spv));
        $this->assertTrue($users->contains($this->admin)); // Admin has access to all
        $this->assertFalse($users->contains($this->user));

        // 3. Combined Filter: Role = SPV and Management = Toilet Inspection
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'spv', 'management' => 'toilet_inspection']));
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->contains($spv));
        $this->assertFalse($users->contains($this->user));
        $this->assertFalse($users->contains($this->admin));
    }

    /**
     * Test summary counts match actual data in database.
     */
    public function test_user_management_summary_counts(): void
    {
        // Before new user creation
        // Total Users: 2 (Admin User, Regular User)
        // Meeting Room: 1 (Regular User has meeting_room)
        // Toilet Inspection: 0
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertViewHas('totalUsers', 2);
        $response->assertViewHas('meetingRoomCount', 1);
        $response->assertViewHas('toiletInspectionCount', 0);

        // Add a toilet inspection SPV
        $spv = User::create([
            'name' => 'Supervisor Toilet',
            'email' => 'spv_toilet@example.com',
            'password' => Hash::make('password'),
            'role' => 'spv',
        ]);
        $spv->modules()->create(['module' => 'toilet_inspection']);

        // New Counts
        // Total Users: 3
        // Meeting Room: 1
        // Toilet Inspection: 1
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertViewHas('totalUsers', 3);
        $response->assertViewHas('meetingRoomCount', 1);
        $response->assertViewHas('toiletInspectionCount', 1);
    }

    /**
     * Test Admin can view edit page and update user details.
     */
    public function test_admin_can_edit_and_update_user(): void
    {
        // View edit page
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->user));
        $response->assertStatus(200);
        $response->assertSee('Edit Pengguna');
        $response->assertSee($this->user->name);

        // Update name, role and module access
        $response2 = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user), [
            'name' => 'Updated Budi',
            'email' => $this->user->email,
            'role' => 'spv',
            'modules' => ['toilet_inspection'],
        ]);

        $response2->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Budi',
            'role' => 'spv',
        ]);

        $this->assertTrue($this->user->fresh()->hasModuleAccess('toilet_inspection'));
        $this->assertFalse($this->user->fresh()->hasModuleAccess('meeting_room'));
    }

    /**
     * Test Admin cannot delete themselves.
     */
    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus pengguna: Anda sedang masuk menggunakan akun pengguna ini.');
        
        // Assert still exists in database
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test Admin cannot delete users with meeting room booking history.
     */
    public function test_cannot_delete_user_with_booking_history(): void
    {
        // Create a booking for regular user
        $room = \App\Models\Room::create([
            'name' => 'Room X',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        \App\Models\Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'title' => 'Important Meeting',
            'booking_date' => date('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->user));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat pemesanan ruang meeting.');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /**
     * Test Admin cannot delete users with toilet inspection history.
     */
    public function test_cannot_delete_user_with_toilet_inspection_history(): void
    {
        // Let's make user an inspector with module access
        $inspector = User::create([
            'name' => 'Inspector A',
            'email' => 'inspector_a@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $inspector->modules()->create(['module' => 'toilet_inspection']);

        $location = \App\Models\ToiletLocation::create([
            'building' => 'Gedung A',
            'floor' => 'Lantai 1',
            'area' => 'Finance',
            'toilet_name' => 'Toilet 1',
            'gender' => 'pria',
        ]);

        \App\Models\Inspection::create([
            'location_id' => $location->id,
            'inspector_id' => $inspector->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $inspector));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat inspeksi toilet yang telah dikirim.');

        $this->assertDatabaseHas('users', ['id' => $inspector->id]);
    }

    /**
     * Test Admin can delete a user who has no booking or inspection history.
     */
    public function test_admin_can_delete_safe_user(): void
    {
        $safeUser = User::create([
            'name' => 'Safe Employee',
            'email' => 'safe@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $safeUser->modules()->create(['module' => 'meeting_room']);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $safeUser));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $safeUser->id]);
        $this->assertDatabaseMissing('user_modules', ['user_id' => $safeUser->id]);
    }

    /**
     * Test user status defaults to 'active'.
     */
    public function test_user_status_defaults_to_active(): void
    {
        $newUser = User::create([
            'name' => 'Test Default Active',
            'email' => 'default_active@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->assertEquals('active', $newUser->fresh()->status);
    }

    /**
     * Test deactivated user cannot log in.
     */
    public function test_deactivated_user_cannot_login(): void
    {
        $inactiveUser = User::create([
            'name' => 'Deactivated User',
            'email' => 'deactivated@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'status' => 'inactive',
        ]);
        $inactiveUser->modules()->create(['module' => 'meeting_room']);

        $response = $this->post('/login', [
            'email' => 'deactivated@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Akun Anda telah dinonaktifkan', session('errors')->first('email'));
    }

    /**
     * Test active session of deactivated user is terminated by middleware.
     */
    public function test_middleware_blocks_deactivated_user_session(): void
    {
        $user = User::create([
            'name' => 'Active To Deactivated',
            'email' => 'active_to_deactivated@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
        $user->modules()->create(['module' => 'meeting_room']);

        // Log in and verify access
        $this->actingAs($user)->get(route('dashboard'))->assertStatus(200);

        // Deactivate user in database
        $user->status = 'inactive';
        $user->save();

        // Access route again -> should be logged out and redirected
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Akun Anda telah dinonaktifkan', session('errors')->first('email'));
    }

    /**
     * Test admin can activate and deactivate users.
     */
    public function test_admin_can_activate_and_deactivate_users(): void
    {
        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
        $targetUser->modules()->create(['module' => 'meeting_room']);

        // Deactivate user
        $response = $this->actingAs($this->admin)->post(route('admin.users.deactivate', $targetUser));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $targetUser->fresh()->status);

        // Activate user
        $response = $this->actingAs($this->admin)->post(route('admin.users.activate', $targetUser));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertEquals('active', $targetUser->fresh()->status);
    }

    /**
     * Test admin cannot deactivate themselves.
     */
    public function test_admin_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.deactivate', $this->admin));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Tidak dapat menonaktifkan pengguna: Anda sedang masuk menggunakan akun pengguna ini.');
        $this->assertEquals('active', $this->admin->fresh()->status);
    }

    /**
     * Test user detail view renders successfully with activity stats.
     */
    public function test_admin_can_view_user_details(): void
    {
        // Create booking for user to generate history
        $room = \App\Models\Room::create([
            'name' => 'Room Detail Test',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'status' => 'active',
        ]);

        \App\Models\Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'title' => 'History Booking',
            'booking_date' => date('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $this->user));
        $response->assertStatus(200);
        $response->assertSee('Detail Profil Pengguna');
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
        $response->assertSee('Ringkasan Aktivitas Ruang Meeting');
        $response->assertSee('Room Detail Test');
        $response->assertSee('History Booking');
    }

    /**
     * Test admin detail view renders admin-specific approval and monitoring summaries.
     */
    public function test_admin_can_view_admin_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $this->admin));
        $response->assertStatus(200);
        $response->assertSee('Detail Profil Pengguna');
        $response->assertSee($this->admin->name);
        $response->assertSee($this->admin->email);
        $response->assertSee('Ringkasan Persetujuan Ruang Meeting');
        $response->assertSee('Pemantauan Inspeksi Toilet');
        $response->assertDontSee('Ringkasan Aktivitas Ruang Meeting');
        $response->assertDontSee('Ringkasan Aktivitas Inspeksi Toilet');
    }

    /**
     * Test user detail dynamically adapts when granted additional modules (e.g. Toilet inspector given SIK).
     */
    public function test_user_detail_adapts_to_granted_modules_and_employee_identity(): void
    {
        $inspector = User::create([
            'name' => 'Petugas Toilet Joko',
            'email' => 'joko.toilet@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
            'employee_id' => 'SBA-PLT-009',
            'department' => 'General Affairs',
            'position' => 'Petugas Inspeksi Toilet',
        ]);
        \App\Models\UserModule::create(['user_id' => $inspector->id, 'module' => 'toilet_inspection']);

        // 1. Initial State: Only Toilet Inspection
        $resBefore = $this->actingAs($this->admin)->get(route('admin.users.show', $inspector));
        $resBefore->assertStatus(200);
        $resBefore->assertSee('Petugas Toilet Joko');
        $resBefore->assertSee('SBA-PLT-009');
        $resBefore->assertSee('General Affairs');
        $resBefore->assertSee('Petugas Inspeksi Toilet');
        $resBefore->assertSee('Ringkasan Aktivitas Inspeksi Toilet');
        $resBefore->assertDontSee('Ringkasan Surat Izin Keluar (SIK)');

        // 2. Grant SIK Module Access to the user
        \App\Models\UserModule::create(['user_id' => $inspector->id, 'module' => 'sik']);

        $permit = \App\Models\ExitPermit::create([
            'sik_number' => 'SIK-2026-TEST-001',
            'user_id' => $inspector->id,
            'approver_id' => $this->admin->id,
            'department' => 'General Affairs',
            'purpose_type' => 'dinas',
            'purpose' => 'Membeli perlengkapan kebersihan',
            'permit_date' => now()->toDateString(),
            'expected_departure_time' => '10:00',
            'expected_return_time' => '12:00',
            'status' => \App\Models\ExitPermit::STATUS_APPROVED,
            'applicant_name_snapshot' => $inspector->name,
            'applicant_department_snapshot' => $inspector->department,
            'applicant_position_snapshot' => $inspector->position,
            'applicant_employee_id_snapshot' => $inspector->employee_id,
        ]);

        // 3. View detail after SIK access is granted: Detail adapts and shows SIK stats & records
        $resAfter = $this->actingAs($this->admin)->get(route('admin.users.show', $inspector));
        $resAfter->assertStatus(200);
        $resAfter->assertSee('Surat Izin Keluar (SIK)');
        $resAfter->assertSee('Ringkasan Aktivitas Inspeksi Toilet');
        $resAfter->assertSee('Ringkasan Surat Izin Keluar (SIK)');
        $resAfter->assertSee('SIK-2026-TEST-001');
        $resAfter->assertSee('Membeli perlengkapan kebersihan');
        $resAfter->assertSee('id="tab-sik"', false);
        $resAfter->assertSee('id="section-sik"', false);

        // 4. Security User detail check
        $securityUser = User::create([
            'name' => 'Danpos Satpam',
            'email' => 'satpam@example.com',
            'password' => Hash::make('password'),
            'role' => 'security',
            'status' => 'active',
            'employee_id' => 'SBA-SEC-001',
            'department' => 'Security & Safety',
            'position' => 'Danpos Security',
        ]);

        $resSec = $this->actingAs($this->admin)->get(route('admin.users.show', $securityUser));
        $resSec->assertStatus(200);
        $resSec->assertSee('Security');
        $resSec->assertSee('Pos Security (SIK Scanner)');
        $resSec->assertSee('Ringkasan Validasi Gerbang Security');
        $resSec->assertSee('Log Scan Gerbang Security');
        $resSec->assertSee('local_police');

        // Check index view as well
        $resIndex = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('local_police');
        $resIndex->assertSee('Pos Security (SIK Scanner)');
    }

    /**
     * Test admin user cannot be deleted at all.
     */
    public function test_cannot_delete_any_admin_user(): void
    {
        $anotherAdmin = User::create([
            'name' => 'Second Admin',
            'email' => 'second_admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $anotherAdmin));
        
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Tidak dapat menghapus pengguna dengan peran Admin.');
        $this->assertDatabaseHas('users', ['id' => $anotherAdmin->id]);
    }

    /**
     * Test last active admin cannot be deactivated.
     */
    public function test_cannot_deactivate_last_active_admin(): void
    {
        $inactiveAdmin = User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive_admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $adminHelper = User::create([
            'name' => 'Admin Helper',
            'email' => 'admin_helper@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Active: $this->admin (active), $adminHelper (active).
        // Deactivate $adminHelper using $this->admin. Should succeed since $this->admin is still active.
        $response = $this->actingAs($this->admin)->post(route('admin.users.deactivate', $adminHelper));
        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $adminHelper->fresh()->status);

        // Update database states directly without changing in-memory attributes.
        // We set $this->admin to inactive, and $adminHelper to active in DB.
        // Now, only $adminHelper is the active admin in DB.
        User::where('id', $this->admin->id)->update(['status' => 'inactive']);
        User::where('id', $adminHelper->id)->update(['status' => 'active']);

        // Try to deactivate $adminHelper using $this->admin (actingAs in-memory active admin).
        // Should fail because $adminHelper is the last active admin in the DB.
        $response = $this->actingAs($this->admin)->post(route('admin.users.deactivate', $adminHelper));
        $response->assertSessionHas('error', 'Tidak dapat menonaktifkan pengguna: Ini adalah satu-satunya akun Admin aktif yang tersisa di sistem.');
        $this->assertEquals('active', $adminHelper->fresh()->status);
    }

    /**
     * Test last active admin cannot have their role modified to a non-admin role.
     */
    public function test_cannot_change_role_of_last_active_admin(): void
    {
        // Try to update $this->admin's role to 'user'
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user',
            'modules' => ['meeting_room'],
        ]);

        $response->assertSessionHas('error', 'Tidak dapat mengubah peran pengguna: Ini adalah satu-satunya akun Admin aktif yang tersisa di sistem.');
        $this->assertEquals('admin', $this->admin->fresh()->role);
    }

    /**
     * Test admin can assign vehicle module to user and it renders in UI.
     */
    public function test_admin_can_assign_vehicle_module_to_user_and_it_renders_in_ui(): void
    {
        // 1. Verify checkbox is present in create view
        $resCreate = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('Kendaraan (Manajemen Kendaraan)');

        // 2. Create user with vehicle module
        $resStore = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Driver User',
            'email' => 'driver.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'modules' => ['vehicle'],
        ]);
        $resStore->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'driver.user@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasModuleAccess('vehicle'));

        // 3. Verify checkbox is present and checked in edit view
        $resEdit = $this->actingAs($this->admin)->get(route('admin.users.edit', $newUser));
        $resEdit->assertOk();
        $resEdit->assertSee('Kendaraan (Manajemen Kendaraan)');
        $resEdit->assertSee('value="vehicle"', false);

        // 4. Verify badge is present in show view
        $resShow = $this->actingAs($this->admin)->get(route('admin.users.show', $newUser));
        $resShow->assertOk();
        $resShow->assertSee('Kendaraan (Manajemen Kendaraan)');

        // 5. Verify index view displays Kendaraan badge and filter
        $resIndex = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Kendaraan');
    }

    /**
     * Test admin can download CSV template for user import.
     */
    public function test_admin_can_download_user_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.template'));
        $response->assertOk();
        $this->assertStringContainsString('template_data_karyawan_sba.csv', (string) $response->headers->get('Content-Disposition'));
    }

    /**
     * Test admin can import users in bulk via CSV.
     */
    public function test_admin_can_import_users_via_csv(): void
    {
        $csvContent = "nama,nip,email,departemen,jabatan,role\n";
        $csvContent .= "Ahmad Dani,19900101,ahmad.dani@sig.id,General Affairs,Staff GA,user\n";
        $csvContent .= "Dewi Sartika,19920202,dewi.sartika@sig.id,Finance,Finance Lead,user\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('karyawan.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.users.import'), [
            'file' => $file,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad.dani@sig.id',
            'employee_id' => '19900101',
            'department' => 'General Affairs',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'dewi.sartika@sig.id',
            'employee_id' => '19920202',
            'department' => 'Finance',
        ]);

        $dani = User::where('email', 'ahmad.dani@sig.id')->first();
        $this->assertTrue($dani->hasModuleAccess('meeting_room'));
        $this->assertTrue($dani->hasModuleAccess('atk'));
        $this->assertTrue($dani->hasModuleAccess('sik'));
        $this->assertTrue($dani->hasModuleAccess('vehicle'));
    }
}

