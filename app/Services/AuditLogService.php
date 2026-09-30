<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogService
{
    /**
     * Record a system audit trail event.
     *
     * @param string $module Module category (e.g. auth, meeting_room, sik, atk, vehicle, user_management, settings)
     * @param string $action Action key (e.g. login, logout, create, update, delete, approve, reject, scan)
     * @param string $description Human readable description of the event
     * @param array|null $details Additional metadata/payload
     * @param User|null $user User actor instance (defaults to auth()->user())
     */
    public static function log(
        string $module,
        string $action,
        string $description,
        ?array $details = null,
        ?User $user = null
    ): ?AuditLog {
        try {
            $currentUser = $user ?: auth()->user();
            $request = request();

            // Resilient safeguard: auto-detect if someone swapped $module and $action
            $knownModules = ['auth', 'meeting_room', 'toilet_inspection', 'atk', 'sik', 'vehicle', 'user_management', 'settings', 'global'];
            if (!in_array($module, $knownModules) && in_array($action, $knownModules)) {
                $temp = $module;
                $module = $action;
                $action = $temp;
            }

            $module = substr((string) $module, 0, 50);
            $action = substr((string) $action, 0, 50);

            $ip = $request ? $request->ip() : '127.0.0.1';
            // Also check for standard reverse proxy header if available
            if ($request && $request->header('X-Forwarded-For')) {
                $ips = explode(',', $request->header('X-Forwarded-For'));
                $ip = trim($ips[0]);
            }

            $userAgent = $request ? substr((string) $request->userAgent(), 0, 500) : null;

            return AuditLog::create([
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? 'Guest/System',
                'user_email' => $currentUser?->email ?? ($details['email'] ?? ($details['identifier'] ?? null)),
                'user_role' => $currentUser?->role ?? 'guest',
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details' => $details,
            ]);
        } catch (Throwable $e) {
            // Never break main business flow if logging fails
            Log::warning('[AuditLog] Failed to record audit log: ' . $e->getMessage());
            return null;
        }
    }
}
