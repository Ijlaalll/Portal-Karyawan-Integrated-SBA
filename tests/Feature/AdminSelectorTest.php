<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminSelectorTest extends TestCase
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
     * Test Admin login redirects to the Global Dashboard page.
     */
    public function test_admin_login_redirects_to_global_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    /**
     * Test Admin global dashboard access and route rendering.
     */
    public function test_admin_can_access_global_dashboard_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.home'));
        $response->assertStatus(200);
        
        // Assert Global Dashboard specific UI elements are visible
        $response->assertSee('Dashboard Admin Global');
        $response->assertSee('Ringkasan Ruang Meeting');
        $response->assertSee('Ringkasan Inspeksi Toilet');
        $response->assertSee('Ringkasan Alat Tulis Kantor');
        $response->assertSee('Ringkasan Surat Izin Keluar (SIK)');
        $response->assertSee('Ringkasan Kendaraan');
        $response->assertSee('Buka Dashboard Ruang Meeting');
        $response->assertSee('Buka Dashboard Inspeksi Toilet');
        
        // Assert deleted elements are not present
        $response->assertDontSee('System Overview');
        $response->assertDontSee('Total Users');
    }

    /**
     * Test Non-Admins are denied access to the Admin home.
     */
    public function test_non_admin_cannot_access_selector_page(): void
    {
        // Regular User -> 403
        $this->actingAs($this->user)->get(route('admin.home'))->assertStatus(403);
        $this->actingAs($this->user)->post(route('admin.select_module'), ['module' => 'meeting_room'])->assertStatus(403);
    }

    /**
     * Test Guest users are redirected to login.
     */
    public function test_guest_cannot_access_selector_page(): void
    {
        // Guest -> Redirect to login
        $this->get(route('admin.home'))->assertRedirect(route('login'));
        $this->post(route('admin.select_module'), ['module' => 'meeting_room'])->assertRedirect(route('login'));
    }

    /**
     * Test Admin module selection stores active module in session.
     */
    public function test_admin_can_select_meeting_room_module(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.select_module'), [
            'module' => 'meeting_room',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('active_admin_module', 'meeting_room');

        // Access dashboard should now work
        $response2 = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'meeting_room',
            'enforce_active_module' => true,
        ])->get(route('admin.dashboard'));
        
        $response2->assertStatus(200);
        
        // Assert Meeting Room specific UI elements are visible
        $response2->assertSee('Reservasi Ruang Meeting');
    }

    /**
     * Test Admin can select Toilet Inspection module.
     */
    public function test_admin_can_select_toilet_inspection_module(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.select_module'), [
            'module' => 'toilet_inspection',
        ]);

        $response->assertRedirect(route('admin.toilet_inspection.dashboard'));
        $response->assertSessionHas('active_admin_module', 'toilet_inspection');

        // Access toilet inspection dashboard should work
        $response2 = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'toilet_inspection',
            'enforce_active_module' => true,
        ])->get(route('admin.toilet_inspection.dashboard'));

        $response2->assertStatus(200);
        
        // Assert Toilet Inspection UI elements are correct
        $response2->assertSee('Manajemen Inspeksi Toilet');
    }

    /**
     * Test Admin accessing module routes without active session module automatically sets it.
     */
    public function test_admin_access_module_without_active_session_sets_session(): void
    {
        $response = $this->actingAs($this->admin)->withSession([
            'enforce_active_module' => true,
        ])->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
        $this->assertEquals('meeting_room', session('active_admin_module'));
    }

    /**
     * Test Admin visiting global dashboard clears active session module.
     */
    public function test_global_dashboard_clears_active_module(): void
    {
        $response = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'meeting_room',
            'enforce_active_module' => true,
        ])->get(route('admin.home'));
        
        $response->assertStatus(200);
        $this->assertNull(session('active_admin_module'));
    }
}
