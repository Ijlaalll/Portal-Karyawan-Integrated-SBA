<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ToiletManagementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ($user->isAdmin() || $user->isSpv() || $user->isGa())) {
            return $next($request);
        }

        abort(403, 'Akses tidak sah. Diperlukan hak akses Manajemen Toilet.');
    }
}
