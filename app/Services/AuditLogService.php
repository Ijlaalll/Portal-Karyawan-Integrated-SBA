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
     */
    public static function log(
        string $action,
        string $description,
        string $module = 'global',
        ?array $details = null,
        ?User $user = null
    ): ?AuditLog {
        try {
            $currentUser = $user ?: auth()->user();
            $request = request();

            $ip = $request ? $request->ip() : 'CLI';
            // Also check for standard reverse proxy header if available
            if ($request && $request->header('X-Forwarded-For')) {
                $ips = explode(',', $request->header('X-Forwarded-For'));
                $ip = trim($ips[0]);
            }

            $userAgent = $request ? substr((string) $request->userAgent(), 0, 500) : null;

            return AuditLog::create([
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? 'Guest/System',
                'user_email' => $currentUser?->email ?? ($details['email'] ?? null),
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
