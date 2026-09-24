<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SikManagementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ($user->isAdmin() || ($user->isGa() && $user->hasModuleAccess('sik')))) {
            return $next($request);
        }

        abort(403, 'Akses tidak sah. Diperlukan hak akses Manajemen SIK.');
    }
}
