<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->getRedirectRoute());
        }

        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginIdentifier = trim($input['email']);
        $password = $input['password'];
        $domain = config('ldap.domain', 'sig.id');

        // Check if the user exists in local database
        $localUser = \App\Models\User::where('email', $loginIdentifier)
            ->orWhere('email', $loginIdentifier . '@' . $domain)
            ->orWhere('employee_id', $loginIdentifier)
            ->first();

        if ($localUser && $localUser->status === 'inactive') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
            ]);
        }

        $authenticatedUser = null;
        $ldapFailureReason = null;

        // 1. Try local database authentication first (fast & reliable offline fallback)
        if ($localUser && \Illuminate\Support\Facades\Hash::check($password, $localUser->password)) {
            $authenticatedUser = $localUser;
            Auth::login($authenticatedUser, $request->boolean('remember'));
        }

        // Determine if identifier is an Active Directory candidate.
        // Active Directory LDAP accounts are either:
        // - Username / NIP without '@' (e.g. '0012345' or 'joko.susilo')
        // - Corporate domain email matching LDAP domain (e.g. 'user@sig.id')
        // Non-domain emails (e.g. '@example.com', '@gmail.com') are strictly local database accounts
        // and must never query the Active Directory LDAP server.
        $isNonLdapEmail = str_contains($loginIdentifier, '@') && !str_ends_with(strtolower($loginIdentifier), '@' . strtolower($domain));
        $isLocalOnlyUser = $localUser && str_contains($localUser->email, '@') && !str_ends_with(strtolower($localUser->email), '@' . strtolower($domain));

        // 2. If not authenticated locally and LDAP is enabled, attempt Active Directory LDAP
        // ONLY query LDAP if it is an eligible candidate and not a local-only user
        if (!$authenticatedUser && config('ldap.enabled', true) && !$isNonLdapEmail && !$isLocalOnlyUser) {
            try {
                $ldapService = app(\App\Services\LdapService::class);
                $ldapResult = $ldapService->authenticate($loginIdentifier, $password);

                if ($ldapResult['success']) {
                    $ldapData = $ldapResult['data'];
                    $ldapEmail = strtolower($ldapData['email']);

                    $authenticatedUser = \App\Models\User::where('email', $ldapEmail)
                        ->orWhere('email', $ldapData['username'] . '@' . $domain)
                        ->first();

                    if ($authenticatedUser) {
                        if ($authenticatedUser->status === 'inactive') {
                            throw ValidationException::withMessages([
                                'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
                            ]);
                        }
                        // Update hashed password for offline fallback
                        $authenticatedUser->password = \Illuminate\Support\Facades\Hash::make($password);
                        if (!empty($ldapData['name']) && empty($authenticatedUser->name)) {
                            $authenticatedUser->name = $ldapData['name'];
                        }
                        if (!empty($ldapData['department']) && empty($authenticatedUser->department)) {
                            $authenticatedUser->department = $ldapData['department'];
                        }
                        if (!empty($ldapData['employee_id']) && empty($authenticatedUser->employee_id)) {
                            $authenticatedUser->employee_id = $ldapData['employee_id'];
                        }
                        $authenticatedUser->save();

                        // If standard LDAP user has no modules assigned yet, initialize baseline modules
                        if ($authenticatedUser->role === 'user' && $authenticatedUser->modules()->count() === 0) {
                            $baselineModules = ['meeting_room', 'atk', 'sik', 'vehicle'];
                            foreach ($baselineModules as $mod) {
                                \App\Models\UserModule::firstOrCreate([
                                    'user_id' => $authenticatedUser->id,
                                    'module' => $mod,
                                ]);
                            }
                        }
                    } else {
                        // Automatically provision user from Active Directory
                        $authenticatedUser = \App\Models\User::create([
                            'name' => $ldapData['name'] ?: $ldapData['username'],
                            'email' => $ldapEmail,
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                            'role' => 'user',
                            'department' => $ldapData['department'] ?? null,
                            'position' => $ldapData['position'] ?? null,
                            'employee_id' => $ldapData['employee_id'] ?? null,
                            'status' => 'active',
                        ]);

                        // Assign default baseline modules (meeting_room, atk, sik, vehicle)
                        // Note: toilet_inspection is specifically excluded as it is reserved for designated staff
                        $baselineModules = ['meeting_room', 'atk', 'sik', 'vehicle'];
                        foreach ($baselineModules as $mod) {
                            \App\Models\UserModule::firstOrCreate([
                                'user_id' => $authenticatedUser->id,
                                'module' => $mod,
                            ]);
                        }
                    }

                    Auth::login($authenticatedUser, $request->boolean('remember'));
                } else {
                    $ldapFailureReason = $ldapResult['error'] ?? null;
                    \Illuminate\Support\Facades\Log::warning('[LDAP Auth Failed]: ' . ($ldapResult['error'] ?? 'Unknown error') . ' | Input: ' . $loginIdentifier);
                }
            } catch (ValidationException $ve) {
                throw $ve;
            } catch (\Throwable $e) {
                $ldapFailureReason = 'Gangguan koneksi LDAP: ' . $e->getMessage();
                \Illuminate\Support\Facades\Log::warning('[LDAP Auth Exception]: ' . $e->getMessage());
            }
        }

        if ($authenticatedUser) {
            $request->session()->regenerate();
            $user = $authenticatedUser;

            // Record audit trail for successful login
            AuditLogService::log(
                'auth',
                'login',
                "Pengguna {$user->name} ({$user->email}) berhasil masuk ke sistem.",
                [
                    'role' => $user->role,
                    'auth_type' => isset($ldapData) ? 'LDAP' : 'Database',
                ],
                $user
            );
            
            $intended = session()->get('url.intended');
            if ($intended) {
                $path = parse_url($intended, PHP_URL_PATH);
                // Meeting Room paths
                $isMeetingRoomPath = preg_match('/^\/(dashboard|rooms|bookings|calendar|facilities)/', $path);
                if ($isMeetingRoomPath && !$user->hasModuleAccess('meeting_room')) {
                    session()->forget('url.intended');
                }
                
                // Toilet Inspection paths
                $isToiletPath = preg_match('/^\/(toilet-inspection|inspections|approvals)/', $path);
                if ($isToiletPath && !$user->hasModuleAccess('toilet_inspection')) {
                    session()->forget('url.intended');
                }

                // ATK paths
                $isAtkPath = preg_match('/^\/atk/', $path);
                if ($isAtkPath && !$user->hasModuleAccess('atk')) {
                    session()->forget('url.intended');
                }

                // SIK paths
                $isSikPath = preg_match('/^\/(sik|sik-management|admin\/sik)/', $path);
                if ($isSikPath && !$user->hasModuleAccess('sik')) {
                    session()->forget('url.intended');
                }

                // Vehicle paths
                $isVehiclePath = preg_match('/^\/vehicles/', $path);
                if ($isVehiclePath && !$user->hasModuleAccess('vehicle')) {
                    session()->forget('url.intended');
                }
            }

            return redirect()->intended($user->getRedirectRoute());
        }

        if ($ldapFailureReason && str_contains($ldapFailureReason, "Can't contact LDAP server")) {
            $errorMessage = "Gagal terhubung ke server Active Directory (LDAP). Jika menggunakan akun domain @{$domain}, pastikan terhubung ke jaringan kantor/VPN, atau gunakan akun lokal database.";
        } else {
            $errorMessage = $ldapFailureReason ?: trans('auth.failed');
        }

        // Record audit trail for failed login attempt
        AuditLogService::log(
            'auth',
            'login_failed',
            "Percobaan login gagal untuk akun '{$loginIdentifier}'. Alasan: {$errorMessage}",
            [
                'identifier' => $loginIdentifier,
                'reason' => $errorMessage,
            ]
        );

        throw ValidationException::withMessages([
            'email' => [$errorMessage],
        ]);
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLogService::log(
                'auth',
                'logout',
                "Pengguna {$user->name} ({$user->email}) telah keluar dari sistem.",
                ['role' => $user->role],
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the unified module selector. (Bypassed in new architecture)
     */
    public function showSelector()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return redirect($user->getRedirectRoute());
    }

    /**
     * Handle unified module selection.
     */
    public function selectModule(Request $request)
    {
        $request->validate([
            'module' => ['required', 'string', 'in:meeting_room,toilet_inspection,atk,sik,vehicle'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $module = $request->input('module');

        if (!$user->hasModuleAccess($module)) {
            abort(403, 'Akses tidak sah ke modul ini.');
        }

        // Record audit trail for module switch
        AuditLogService::log(
            $module,
            'switch_module',
            "Pengguna {$user->name} beralih ke modul {$module}.",
            ['module' => $module],
            $user
        );

        session(['active_module' => $module]);
        if ($user->isAdmin()) {
            session(['active_admin_module' => $module]);
        }

        if ($module === 'meeting_room') {
            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('dashboard');
        }

        if ($module === 'atk') {
            return $user->isAdmin()
                ? redirect()->route('atk.dashboard')
                : redirect()->route('atk.catalog');
        }

        if ($module === 'sik') {
            if ($user->isSecurity()) {
                return redirect()->route('sik.scanner');
            }
            if ($user->isAdmin() || $user->isGa()) {
                return redirect()->route('admin.sik.dashboard');
            }
            return redirect()->route('sik.index');
        }

        if ($module === 'vehicle') {
            return redirect()->route('vehicles.requests.index');
        }

        return $user->isAdmin()
            ? redirect()->route('admin.toilet_inspection.dashboard')
            : redirect()->route('toilet_inspection.dashboard');
    }

    /**
     * Show no-access screen for users without any modules.
     */
    public function noAccess()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin() || $user->modules()->exists()) {
            return redirect($user->getRedirectRoute());
        }

        return view('auth.no_access');
    }
}
