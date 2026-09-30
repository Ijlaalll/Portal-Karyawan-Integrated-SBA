<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        // 2. If not authenticated locally and LDAP is enabled, attempt Active Directory LDAP
        if (!$authenticatedUser && config('ldap.enabled', true)) {
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
                        if (!empty($ldapData['position']) && empty($authenticatedUser->position)) {
                            $authenticatedUser->position = $ldapData['position'];
                        }
                        $authenticatedUser->save();
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

                        // Assign default baseline modules
                        \App\Models\UserModule::firstOrCreate([
                            'user_id' => $authenticatedUser->id,
                            'module' => 'sik',
                        ]);
                        \App\Models\UserModule::firstOrCreate([
                            'user_id' => $authenticatedUser->id,
                            'module' => 'meeting_room',
                        ]);
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

        $errorMessage = $ldapFailureReason ?: trans('auth.failed');
        throw ValidationException::withMessages([
            'email' => [$errorMessage],
        ]);
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
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
