<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserModule;
use App\Services\AuditLogService;
use App\Services\NavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator System',
            'email' => 'admin@sig.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular Employee',
            'email' => 'employee@sig.id',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.settings.audit-logs.index'));
        $response->assertStatus(403);
    }

    public function test_login_and_logout_create_audit_logs(): void
    {
        // 1. Successful login
        $resLogin = $this->post('/login', [
            'email' => 'employee@sig.id',
            'password' => 'password123',
        ]);
        $resLogin->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_email' => 'employee@sig.id',
            'module' => 'auth',
            'action' => 'login',
        ]);

        // 2. Logout
        $resLogout = $this->actingAs($this->regularUser)->post('/logout');
        $resLogout->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_email' => 'employee@sig.id',
            'module' => 'auth',
            'action' => 'logout',
        ]);

        // 3. Failed login
        $resFailed = $this->post('/login', [
            'email' => 'unknown@sig.id',
            'password' => 'wrongpassword',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'auth',
            'action' => 'login_failed',
        ]);
    }

    public function test_admin_can_access_audit_logs_index_page(): void
    {
        AuditLogService::log(
            'auth',
            'login',
            'Pengguna login ke sistem',
            ['foo' => 'bar'],
            $this->regularUser
        );

        $response = $this->actingAs($this->admin)->get(route('admin.settings.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
        $response->assertSee('Log Aktivitas');
        $response->assertSee('Pengguna login ke sistem');
        $response->assertSee($this->regularUser->email);
    }

    public function test_audit_logs_filtering_and_search(): void
    {
        AuditLog::create([
            'user_id' => $this->regularUser->id,
            'user_name' => $this->regularUser->name,
            'user_email' => $this->regularUser->email,
            'user_role' => $this->regularUser->role,
            'module' => 'auth',
            'action' => 'login',
            'description' => 'User login via Active Directory',
            'ip_address' => '192.168.1.100',
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_email' => $this->admin->email,
            'user_role' => $this->admin->role,
            'module' => 'user_management',
            'action' => 'update_user',
            'description' => 'Admin updated role for user',
            'ip_address' => '127.0.0.1',
        ]);

        // Search by IP
        $resIp = $this->actingAs($this->admin)->get(route('admin.settings.audit-logs.index', ['search' => '192.168.1.100']));
        $resIp->assertStatus(200);
        $resIp->assertSee('192.168.1.100');
        $resIp->assertDontSee('Admin updated role for user');

        // Filter by Module
        $resModule = $this->actingAs($this->admin)->get(route('admin.settings.audit-logs.index', ['module' => 'user_management']));
        $resModule->assertStatus(200);
        $resModule->assertSee('Admin updated role for user');
        $resModule->assertDontSee('User login via Active Directory');
    }

    public function test_admin_can_export_audit_logs_to_csv(): void
    {
        AuditLog::create([
            'user_id' => $this->regularUser->id,
            'user_name' => 'Amrullah Nurdin',
            'user_email' => 'amrullah.nurdin@sig.id',
            'user_role' => 'user',
            'module' => 'sik',
            'action' => 'create_sik',
            'description' => 'Membuat draft SIK',
            'ip_address' => '10.20.30.40',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.settings.audit-logs.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('amrullah.nurdin@sig.id', $response->streamedContent());
        $this->assertStringContainsString('10.20.30.40', $response->streamedContent());
    }

    public function test_navigation_includes_audit_trail_in_settings_menu_for_admin(): void
    {
        $navService = app(NavigationService::class);
        $menu = $navService->getMenuForUser($this->admin);

        $settingsItem = collect($menu)->firstWhere('id', 'settings_menu');
        $this->assertNotNull($settingsItem, 'Settings menu must exist for Admin');

        $submenuTitles = collect($settingsItem['submenu'])->pluck('title')->toArray();
        $this->assertContains('Audit Trail (Log Aktivitas)', $submenuTitles);
    }

    public function test_regular_user_with_sik_access_can_view_sik_menu_without_employee_profile(): void
    {
        // User has SIK module access but empty department (like amrullah.nurdin before profile update)
        $this->regularUser->department = null;
        $this->regularUser->position = null;
        $this->regularUser->employee_id = null;
        $this->regularUser->save();

        UserModule::create([
            'user_id' => $this->regularUser->id,
            'module' => 'sik',
        ]);

        $navService = app(NavigationService::class);
        $menu = $navService->getMenuForUser($this->regularUser);

        $sikGroup = collect($menu)->firstWhere('id', 'sik_menu');
        $this->assertNotNull($sikGroup, 'SIK module menu must be visible to regular user with sik access');

        $submenuTitles = collect($sikGroup['submenu'])->pluck('title')->toArray();
        $this->assertContains('Ajukan SIK', $submenuTitles);
        $this->assertContains('SIK Saya', $submenuTitles);
    }

    public function test_toilet_inspection_module_is_not_given_by_default_and_adapts_to_role_when_granted(): void
    {
        $navService = app(NavigationService::class);

        // 1. Without toilet_inspection module, menu is hidden
        $menuWithout = $navService->getMenuForUser($this->regularUser);
        $this->assertNull(collect($menuWithout)->firstWhere('id', 'toilet_inspection_menu'));

        // 2. When admin grants toilet_inspection module to regular user (petugas)
        UserModule::create([
            'user_id' => $this->regularUser->id,
            'module' => 'toilet_inspection',
        ]);
        $this->regularUser->load('modules');

        $menuWithUser = $navService->getMenuForUser($this->regularUser);
        $toiletMenuUser = collect($menuWithUser)->firstWhere('id', 'toilet_inspection_menu');
        $this->assertNotNull($toiletMenuUser);
        $userSubmenu = collect($toiletMenuUser['submenu'])->pluck('title')->toArray();
        $this->assertContains('Mulai Inspeksi', $userSubmenu); // Petugas action
        $this->assertNotContains('Persetujuan', $userSubmenu); // Not SPV

        // 3. When role is SPV (supervisor/pemantau)
        $spvUser = User::create([
            'name' => 'Supervisor Toilet',
            'email' => 'spv.toilet@sig.id',
            'password' => bcrypt('password123'),
            'role' => 'spv',
            'status' => 'active',
        ]);
        UserModule::create([
            'user_id' => $spvUser->id,
            'module' => 'toilet_inspection',
        ]);

        $menuSpv = $navService->getMenuForUser($spvUser);
        $toiletMenuSpv = collect($menuSpv)->firstWhere('id', 'toilet_inspection_menu');
        $this->assertNotNull($toiletMenuSpv);
        $spvSubmenu = collect($toiletMenuSpv['submenu'])->pluck('title')->toArray();
        $this->assertContains('Persetujuan', $spvSubmenu);
        $this->assertContains('Laporan', $spvSubmenu);
        $this->assertNotContains('Mulai Inspeksi', $spvSubmenu);
    }
}
