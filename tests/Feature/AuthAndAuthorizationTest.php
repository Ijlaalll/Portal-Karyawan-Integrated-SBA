<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Guest -> protected route -> Redirected to login.
     */
    public function test_guest_is_redirected_from_protected_routes(): void
    {
        // Guest accessing user area
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));

        // Guest accessing admin area
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test User -> user area -> ALLOWED, User -> admin area -> DENIED.
     */
    public function test_user_can_access_user_area_but_not_admin_area(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Access user area
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Selamat datang kembali, John Doe');

        // Access admin area
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test Admin -> admin area -> ALLOWED.
     */
    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::create([
            'name' => 'Jane Admin',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Access admin area
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Selamat datang kembali, Jane Admin');
    }

    /**
     * Test Login: Valid credentials -> LOGIN SUCCESS.
     */
    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'User Test',
            'email' => 'usertest@example.com',
            'password' => Hash::make('correct-password'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'usertest@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test Login: Invalid credentials -> LOGIN FAILED.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'User Test',
            'email' => 'usertest@example.com',
            'password' => Hash::make('correct-password'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'usertest@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test Logout: Authenticated user -> LOGOUT -> session invalidated -> protected route inaccessible.
     */
    public function test_logout_invalidates_session_and_restricts_access(): void
    {
        $user = User::create([
            'name' => 'User Test',
            'email' => 'usertest@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        // Perform logout
        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // Try to access protected route again
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test Role Manipulation: Ensuring no public way to modify role.
     */
    public function test_role_manipulation_protection(): void
    {
        // Since there is no user registration route, we verify that the register route is indeed not registered.
        $response = $this->get('/register');
        $response->assertStatus(404);

        $response = $this->post('/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $response->assertStatus(404);
    }

    /**
     * Scenario A: Meeting Room only redirects to meeting room dashboard.
     */
    public function test_redirect_scenario_a_meeting_room_only(): void
    {
        $user = User::create([
            'name' => 'MR User',
            'email' => 'mr@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user->modules()->create(['module' => 'meeting_room']);

        $response = $this->post('/login', [
            'email' => 'mr@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Scenario B: Toilet Inspection only redirects to toilet inspection dashboard.
     */
    public function test_redirect_scenario_b_toilet_inspection_only(): void
    {
        $user = User::create([
            'name' => 'TI User',
            'email' => 'ti@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user->modules()->create(['module' => 'toilet_inspection']);

        $response = $this->post('/login', [
            'email' => 'ti@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('toilet_inspection.dashboard'));
    }

    /**
     * Scenario C: Both managements redirects to the unified selector.
     */
    public function test_redirect_scenario_c_both_managements(): void
    {
        $user = User::create([
            'name' => 'Both User',
            'email' => 'both@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user->modules()->create(['module' => 'meeting_room']);
        $user->modules()->create(['module' => 'toilet_inspection']);

        $response = $this->post('/login', [
            'email' => 'both@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Choose meeting room
        $responseMR = $this->actingAs($user)->post(route('select-module.post'), ['module' => 'meeting_room']);
        $responseMR->assertRedirect(route('dashboard'));
        $this->assertEquals('meeting_room', session('active_module'));

        // Choose toilet inspection
        $responseTI = $this->actingAs($user)->post(route('select-module.post'), ['module' => 'toilet_inspection']);
        $responseTI->assertRedirect(route('toilet_inspection.dashboard'));
        $this->assertEquals('toilet_inspection', session('active_module'));
    }

    /**
     * Scenario D: Admin redirects to selector.
     */
    public function test_redirect_scenario_d_admin(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    /**
     * Scenario E: No management access redirects to no-access page.
     */
    public function test_redirect_scenario_e_no_management_access(): void
    {
        $user = User::create([
            'name' => 'No Access User',
            'email' => 'noaccess@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Use test flag to bypass testing fallback
        session(['test_no_management_access' => true]);

        $response = $this->post('/login', [
            'email' => 'noaccess@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('no-access'));

        // Accessing meeting room dashboard directly should redirect to no-access
        $responseDash = $this->actingAs($user)->get('/dashboard');
        $responseDash->assertRedirect(route('no-access'));
    }

    /**
     * Test complete session lifecycle: logout from unauthorized states and re-login.
     */
    public function test_logout_and_login_lifecycle(): void
    {
        $tiUser = User::create([
            'name' => 'TI User',
            'email' => 'ti@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $tiUser->modules()->create(['module' => 'toilet_inspection']);

        // Try to access meeting room dashboard (unauthorized) -> returns 403
        $response = $this->actingAs($tiUser)->get('/dashboard');
        $response->assertStatus(403);

        // Perform logout from 403 / session state
        $responseLogout = $this->post('/logout');
        $responseLogout->assertRedirect(route('login'));
        $this->assertGuest();

        // Login again and verify we get correct redirect
        $responseLogin = $this->post('/login', [
            'email' => 'ti@example.com',
            'password' => 'password',
        ]);
        $responseLogin->assertRedirect(route('toilet_inspection.dashboard'));
    }
}
