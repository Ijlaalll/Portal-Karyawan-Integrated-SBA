<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LdapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class LdapAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_ldap_test_runs_successfully(): void
    {
        $mockService = Mockery::mock(LdapService::class);
        $mockService->shouldReceive('testConnection')->once()->andReturn([
            'host' => '10.60.12.44',
            'port' => 389,
            'domain' => 'sig.id',
            'php_ldap_loaded' => true,
            'network_reachable' => true,
            'network_latency_ms' => 12.5,
            'error' => null,
        ]);
        $mockService->shouldReceive('authenticate')->once()->with('joko.susilo', 'secret123')->andReturn([
            'success' => true,
            'driver' => 'php-ldap',
            'data' => [
                'username' => 'joko.susilo',
                'name' => 'Joko Susilo',
                'email' => 'joko.susilo@sig.id',
                'department' => 'Information Technology',
                'position' => 'Staff IT',
                'employee_id' => '12345678',
            ],
        ]);

        $this->app->instance(LdapService::class, $mockService);

        $this->artisan('ldap:test', ['username' => 'joko.susilo', 'password' => 'secret123'])
            ->expectsOutputToContain('UJI KONEKSI & AUTENTIKASI LDAP ACTIVE DIRECTORY (SIG)')
            ->expectsOutputToContain('[OK] Ekstensi PHP "ldap" aktif.')
            ->expectsOutputToContain('[OK] Terhubung ke 10.60.12.44:389')
            ->expectsOutputToContain('[BERHASIL] Autentikasi Active Directory Sukses!')
            ->expectsOutputToContain('Joko Susilo')
            ->assertExitCode(0);
    }

    public function test_login_authenticates_via_ldap_and_provisions_user(): void
    {
        $mockService = Mockery::mock(LdapService::class);
        $mockService->shouldReceive('authenticate')->once()->with('ahmad.sig', 'password_ldap')->andReturn([
            'success' => true,
            'driver' => 'php-ldap',
            'data' => [
                'username' => 'ahmad.sig',
                'name' => 'Ahmad SBA',
                'email' => 'ahmad.sig@sig.id',
                'department' => 'Produksi',
                'position' => 'Operator',
                'employee_id' => '998877',
            ],
        ]);

        $this->app->instance(LdapService::class, $mockService);

        $response = $this->post('/login', [
            'email' => 'ahmad.sig',
            'password' => 'password_ldap',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        $user = User::where('email', 'ahmad.sig@sig.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Ahmad SBA', $user->name);
        $this->assertEquals('Produksi', $user->department);
        $this->assertEquals('998877', $user->employee_id);
    }

    public function test_local_user_can_still_login_even_if_ldap_is_offline(): void
    {
        $user = User::create([
            'name' => 'Local Admin',
            'email' => 'admin@sba.co.id',
            'password' => Hash::make('localpassword'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Mock LDAP service to ensure it is NOT even called when local authentication succeeds
        $mockService = Mockery::mock(LdapService::class);
        $mockService->shouldNotReceive('authenticate');
        $this->app->instance(LdapService::class, $mockService);

        $response = $this->post('/login', [
            'email' => 'admin@sba.co.id',
            'password' => 'localpassword',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }
}
