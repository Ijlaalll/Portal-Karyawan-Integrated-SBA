<?php

namespace App\Http\Controllers\Sik;

use App\Http\Controllers\Controller;
use App\Models\ExitPermit;
use App\Models\SikScanLog;
use App\Services\SikScanService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SikSecurityController extends Controller
{
    public function __construct(
        protected SikScanService $sikScanService
    ) {}

    /**
     * Display Security Operational Dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user->isSecurity() && !$user->isAdmin()) {
            abort(403, 'Akses khusus Petugas Security atau Administrator.');
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // 6 Summary Metric Counts (asia/jakarta timezone)
        $totalPermits = ExitPermit::count();
        $pendingPermits = ExitPermit::where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $activeOutside = ExitPermit::where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $totalOverdue = ExitPermit::overdue()->count();
        $returnedToday = ExitPermit::where('status', ExitPermit::STATUS_RETURNED)
            ->whereDate('actual_return_at', $today)
            ->count();
        $archivedCount = ExitPermit::whereIn('status', [
            ExitPermit::STATUS_REJECTED,
            ExitPermit::STATUS_REVOKED,
            ExitPermit::STATUS_CLOSED_MANUALLY,
        ])->count();

        // Operational Table: Active outside permits (OUTSIDE or OVERDUE)
        $activeOutsidePermits = ExitPermit::with(['user', 'approver', 'exitScannedBy'])
            ->where('status', ExitPermit::STATUS_OUTSIDE)
            ->orderBy('actual_departure_at', 'asc')
            ->get();

        // Recent 5 Gate Scans for quick visibility
        $recentScans = SikScanLog::with(['exitPermit.user', 'security'])
            ->orderBy('scanned_at', 'desc')
            ->limit(5)
            ->get();

        return view('sik.security.dashboard', compact(
            'totalPermits',
            'pendingPermits',
            'activeOutside',
            'totalOverdue',
            'returnedToday',
            'archivedCount',
            'activeOutsidePermits',
            'recentScans'
        ));
    }

    /**
     * Display Security QR Scanner interface.
     */
    public function scanner(Request $request)
    {
        $user = $request->user();
        if (!$user->isSecurity()) {
            abort(403, 'Akses khusus Petugas Security.');
        }

        return view('sik.security.scanner');
    }

    /**
     * Validate scanned QR token via AJAX / POST.
     */
    public function validateQr(Request $request)
    {
        $user = $request->user();
        if (!$user->isSecurity()) {
            return response()->json([
                'valid' => false,
                'message' => 'Akses ditolak: Hanya Petugas Security yang dapat memvalidasi QR SIK.',
            ], 403);
        }

        $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $result = $this->sikScanService->validateToken($request->input('qr_token'));

        if (!$result['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $result['message'],
                'permit' => isset($result['permit']) ? [
                    'sik_number' => $result['permit']->sik_number,
                    'user_name' => $result['permit']->user->name ?? '-',
                    'department' => $result['permit']->department,
                    'status' => $result['permit']->status,
                ] : null,
            ]);
        }

        $permit = $result['permit'];

        return response()->json([
            'valid' => true,
            'action' => $result['action'], // 'EXIT' or 'RETURN'
            'message' => $result['message'],
            'is_overdue' => $result['is_overdue'] ?? false,
            'permit' => [
                'id' => $permit->id,
                'sik_number' => $permit->sik_number,
                'user_name' => $permit->user->name ?? '-',
                'employee_id' => $permit->employee_id ?: ($permit->user->employee_id ?: '-'),
                'department' => $permit->department,
                'position' => $permit->position ?: ($permit->user->position ?: '-'),
                'purpose_type' => strtoupper($permit->purpose_type),
                'purpose' => $permit->purpose,
                'purpose_detail' => $permit->purpose_detail,
                'permit_date' => $permit->permit_date->format('d M Y'),
                'expected_departure_time' => $permit->expected_departure_time,
                'expected_return_time' => $permit->expected_return_time,
                'vehicle_type' => $permit->vehicle_type ?: 'none',
                'vehicle_details' => $permit->vehicle_details ?: '-',
                'actual_departure_at' => $permit->actual_departure_at ? $permit->actual_departure_at->format('d M Y H:i') : null,
                'status' => $permit->status,
                'approver_name' => $permit->approver ? $permit->approver->name : '-',
            ],
        ]);
    }

    /**
     * Confirm Exit at Gate.
     */
    public function confirmExit(Request $request, ExitPermit $sik)
    {
        $user = $request->user();
        if (!$user->isSecurity()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Akses ditolak: Hanya Petugas Security yang dapat memproses scan keluar.'], 403);
            }
            abort(403, 'Akses ditolak: Hanya Petugas Security yang dapat memproses scan keluar.');
        }

        $request->validate([
            'gate' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $gateName = $request->input('gate', 'Pos Utama / Main Gate');
        $permit = $this->sikScanService->confirmExit(
            $sik->id,
            $user,
            $gateName,
            $request->input('notes')
        );

        AuditLogService::log(
            'sik',
            'scan_exit',
            "Petugas Security mengonfirmasi KELUAR untuk SIK #{$permit->sik_number} ({$permit->user->name}) via {$gateName}",
            [
                'sik_id' => $permit->id,
                'sik_number' => $permit->sik_number,
                'user_name' => $permit->user->name ?? null,
                'gate' => $gateName,
            ],
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pemohon SIK #{$permit->sik_number} ({$permit->user->name}) telah dikonfirmasi KELUAR.",
                'permit' => $permit,
            ]);
        }

        return redirect()->route('sik.scanner')
            ->with('success', "Pemohon SIK #{$permit->sik_number} ({$permit->user->name}) telah dikonfirmasi KELUAR.");
    }

    /**
     * Confirm Return at Gate.
     */
    public function confirmReturn(Request $request, ExitPermit $sik)
    {
        $user = $request->user();
        if (!$user->isSecurity()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Akses ditolak: Hanya Petugas Security yang dapat memproses scan kembali.'], 403);
            }
            abort(403, 'Akses ditolak: Hanya Petugas Security yang dapat memproses scan kembali.');
        }

        $request->validate([
            'gate' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $gateName = $request->input('gate', 'Pos Utama / Main Gate');
        $permit = $this->sikScanService->confirmReturn(
            $sik->id,
            $user,
            $gateName,
            $request->input('notes')
        );

        AuditLogService::log(
            'sik',
            'scan_return',
            "Petugas Security mengonfirmasi KEMBALI untuk SIK #{$permit->sik_number} ({$permit->user->name}) via {$gateName}",
            [
                'sik_id' => $permit->id,
                'sik_number' => $permit->sik_number,
                'user_name' => $permit->user->name ?? null,
                'gate' => $gateName,
            ],
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pemohon SIK #{$permit->sik_number} ({$permit->user->name}) telah dikonfirmasi KEMBALI.",
                'permit' => $permit,
            ]);
        }

        return redirect()->route('sik.scanner')
            ->with('success', "Pemohon SIK #{$permit->sik_number} ({$permit->user->name}) telah dikonfirmasi KEMBALI.");
    }

    /**
     * Display Security Gate Scan Logs / Audit Trail.
     */
    public function scanLogs(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isSecurity() && !($user->isGa() && $user->hasModuleAccess('sik'))) {
            abort(403, 'Akses khusus Security, Administrator, atau GA SIK.');
        }

        $query = SikScanLog::with(['exitPermit.user', 'security'])
            ->orderBy('scanned_at', 'desc');

        // Scan Type Filter
        if ($request->filled('scan_type') && in_array($request->input('scan_type'), ['EXIT', 'RETURN'])) {
            $query->where('scan_type', $request->input('scan_type'));
        }

        // Date Filter
        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->input('date'));
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('gate', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('exitPermit', function ($sub) use ($search) {
                      $sub->where('sik_number', 'like', "%{$search}%")
                          ->orWhere('department', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($u) use ($search) {
                              $u->where('name', 'like', "%{$search}%");
                          });
                  });
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('sik.security.scan_logs', compact('logs'));
    }
}
