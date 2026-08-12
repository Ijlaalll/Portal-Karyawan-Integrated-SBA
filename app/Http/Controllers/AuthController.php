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
                'email' => ['Your account has been deactivated. Please contact the administrator.'],
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
     * Show the unified module selector.
     */
    public function showSelector()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin can always access the selector.
        // Normal user must have access to multiple modules.
        if (!$user->isAdmin()) {
            $modules = $user->modules()->pluck('module')->toArray();
            if (count($modules) < 2) {
                return redirect($user->getRedirectRoute());
            }
        }

        // Clear active module states when visiting the selector page
        session()->forget('active_module');
        session()->forget('active_admin_module');

        return view('auth.select_module');
    }

    /**
     * Handle unified module selection.
     */
    public function selectModule(Request $request)
    {
        $request->validate([
            'module' => ['required', 'string', 'in:meeting_room,toilet_inspection'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $module = $request->input('module');

        if (!$user->hasModuleAccess($module)) {
            abort(403, 'Unauthorized access to this module.');
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
