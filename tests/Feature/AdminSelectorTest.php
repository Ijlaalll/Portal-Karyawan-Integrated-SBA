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
     * Test Admin login redirects to the Selection page.
     */
    public function test_admin_login_redirects_to_selector(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    /**
     * Test Admin selector access and route rendering.
     */
    public function test_admin_can_access_selector_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.home'));
        $response->assertStatus(200);
        $response->assertSee('Welcome back, System Admin');
        $response->assertSee('Meeting Room Management');
        $response->assertSee('Toilet Inspection Management');
        $response->assertSee('Open Management');
        
        // Assert selector layout is management-neutral
        $response->assertSee('Management Selection');
        $response->assertDontSee('Meeting Room Reservation');
        $response->assertDontSee('New Room');
        $response->assertSee('Select a management area to begin.');
    }

    /**
     * Test Non-Admins are denied access to the Admin selector.
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

        // Access dashboard should now work when session enforcement is on
        $response2 = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'meeting_room',
            'enforce_active_module' => true,
        ])->get(route('admin.dashboard'));
        
        $response2->assertStatus(200);
        
        // Assert Meeting Room specific UI elements are visible
        $response2->assertSee('Meeting Room Reservation');
        $response2->assertSee('New Room');
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

        // Access toilet inspection dashboard should work when session enforcement is on
        $response2 = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'toilet_inspection',
            'enforce_active_module' => true,
        ])->get(route('admin.toilet_inspection.dashboard'));

        $response2->assertStatus(200);
        
        // Assert Toilet Inspection UI elements are neutral/correct
        $response2->assertSee('Toilet Inspection Management');
        $response2->assertDontSee('New Room');
        $response2->assertDontSee('Meeting Room Reservation');
    }

    /**
     * Test Admin is blocked from module routes without active session module.
     */
    public function test_admin_redirected_to_selector_without_active_session(): void
    {
        // Accessing dashboard without session set, but with enforcement on
        $response = $this->actingAs($this->admin)->withSession([
            'enforce_active_module' => true,
        ])->get(route('admin.dashboard'));
        
        $response->assertRedirect(route('admin.home'));
        $response->assertSessionHas('error', 'Please select a management module first.');
    }

    /**
     * Test Admin visiting selection page clears active session module.
     */
    public function test_selector_clears_active_module(): void
    {
        $response = $this->actingAs($this->admin)->withSession([
            'active_admin_module' => 'meeting_room',
            'enforce_active_module' => true,
        ])->get(route('admin.home'));
        
        $response->assertStatus(200);
        $this->assertNull(session('active_admin_module'));
    }
}
