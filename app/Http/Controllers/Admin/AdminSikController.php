<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentApproval;
use App\Models\ExitPermit;
use App\Models\SikApprovalHistory;
use App\Models\SikScanLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminSikController extends Controller
{
    /**
     * Display the Administrative SIK Dashboard.
     */
    public function dashboard(Request $request)
    {
        $periodService = app(\App\Services\DashboardPeriodService::class);
        $periodInfo = $periodService->parsePeriod($request, 'period', 'date', 'monthly');
        $start = $periodInfo['start_date'];
        $end = $periodInfo['end_date'];
        $startDateStr = $start->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        // Query within the active period based on permit_date
        $periodQuery = ExitPermit::whereDate('permit_date', '>=', $startDateStr)
            ->whereDate('permit_date', '<=', $endDateStr);

        $totalPermits = (clone $periodQuery)->count();
        $pendingPermits = (clone $periodQuery)->where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $totalPendingBacklog = ExitPermit::where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $approvedToday = (clone $periodQuery)->where('status', ExitPermit::STATUS_APPROVED)->count();
        $activeOutside = (clone $periodQuery)->where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $totalOverdue = (clone $periodQuery)->overdue()->count();
        $returnedToday = (clone $periodQuery)->where('status', ExitPermit::STATUS_RETURNED)->count();
        $totalRejected = (clone $periodQuery)->where('status', ExitPermit::STATUS_REJECTED)->count();
        $totalRevoked = (clone $periodQuery)->where('status', ExitPermit::STATUS_REVOKED)->count();
        $totalClosedManually = (clone $periodQuery)->where('status', ExitPermit::STATUS_CLOSED_MANUALLY)->count();

        // Purpose Breakdown in Period
        $dinasCount = (clone $periodQuery)->where('purpose_type', 'dinas')->count();
        $pribadiCount = (clone $periodQuery)->where('purpose_type', 'pribadi')->count();

        // Department breakdown in Period
        $departments = ExitPermit::select('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        $deptStats = [];
        foreach ($departments as $dept) {
            $deptQuery = (clone $periodQuery)->where('department', $dept);
            $deptStats[] = [
                'department' => $dept,
                'total' => (clone $deptQuery)->count(),
                'outside' => (clone $deptQuery)->where('status', ExitPermit::STATUS_OUTSIDE)->count(),
                'overdue' => (clone $deptQuery)->overdue()->count(),
                'pending' => (clone $deptQuery)->where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count(),
            ];
        }

        // Recent 10 Permits (in period, with fallback to latest)
        $recentPermits = (clone $periodQuery)->with(['user', 'approver', 'exitScannedBy', 'returnScannedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        if ($recentPermits->isEmpty()) {
            $recentPermits = ExitPermit::with(['user', 'approver', 'exitScannedBy', 'returnScannedBy'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Recent 10 Gate Scans
        $recentScans = SikScanLog::with(['exitPermit.user', 'security'])
            ->orderBy('scanned_at', 'desc')
            ->limit(8)
            ->get();

        return view('sik.admin.dashboard', compact(
            'periodInfo',
            'totalPermits',
            'pendingPermits',
            'totalPendingBacklog',
            'approvedToday',
            'activeOutside',
            'totalOverdue',
            'returnedToday',
            'totalRejected',
            'totalRevoked',
            'totalClosedManually',
            'dinasCount',
            'pribadiCount',
            'deptStats',
            'recentPermits',
            'recentScans'
        ));
    }

    /**
     * Display all Exit Permits across all departments (Semua SIK).
     */
    public function index(Request $request)
    {
        $query = ExitPermit::with(['user', 'approver', 'exitScannedBy', 'returnScannedBy'])
            ->orderBy('created_at', 'desc');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sik_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Department Filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        // Purpose Type Filter
        if ($request->filled('purpose_type') && in_array($request->input('purpose_type'), ['dinas', 'pribadi'])) {
            $query->where('purpose_type', $request->input('purpose_type'));
        }

        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $status);
            }
        }

        // Date Filter
        if ($request->filled('date')) {
            $query->where('permit_date', $request->input('date'));
        }

        $permits = $query->paginate(15)->withQueryString();

        $departments = ExitPermit::select('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        // Summary Counts
        $totalCount = ExitPermit::count();
        $pendingCount = ExitPermit::where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $outsideCount = ExitPermit::where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $overdueCount = ExitPermit::overdue()->count();
        $returnedCount = ExitPermit::where('status', ExitPermit::STATUS_RETURNED)->count();

        return view('sik.admin.index', compact(
            'permits',
            'departments',
            'totalCount',
            'pendingCount',
            'outsideCount',
            'overdueCount',
            'returnedCount'
        ));
    }

    /**
     * Display the SIK Approval and Action Audit Logs.
     */
    public function auditLogs(Request $request)
    {
        $query = SikApprovalHistory::with(['exitPermit.user', 'approver'])
            ->orderBy('actioned_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('exitPermit', function ($sub) use ($search) {
                      $sub->where('sik_number', 'like', "%{$search}%")
                          ->orWhere('department', 'like', "%{$search}%");
                  })
                  ->orWhereHas('approver', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('sik.admin.audit_logs', compact('logs'));
    }
}
