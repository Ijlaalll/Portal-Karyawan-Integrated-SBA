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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if the user is deactivated
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if ($user && $user->status === 'inactive') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
            ]);
        }
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Redirect based on user role and assigned modules
            $user = Auth::user();
            
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

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
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
