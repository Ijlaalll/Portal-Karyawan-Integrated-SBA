<?php

namespace App\Http\Controllers\Sik;

use App\Http\Controllers\Controller;
use App\Models\ExitPermit;
use App\Services\DepartmentApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SikDepartmentController extends Controller
{
    public function __construct(
        protected DepartmentApprovalService $departmentApprovalService
    ) {}

    /**
     * Display department SIK monitoring dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $managedDepts = $this->departmentApprovalService->getManagedDepartments($user);

        if (!$user->isAdmin() && !$user->isGa() && empty($managedDepts)) {
            abort(403, 'Anda tidak memiliki kewenangan mengelola SIK departemen.');
        }

        if (($user->isAdmin() || $user->isGa()) && empty($managedDepts)) {
            $managedDepts = ExitPermit::select('department')->distinct()->pluck('department')->filter()->values()->toArray();
        }

        $query = ExitPermit::with(['user', 'approver', 'exitScannedBy', 'returnScannedBy'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin() && !$user->isGa()) {
            $query->whereIn('department', $managedDepts);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sik_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Department Filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
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

        // Calculate statistics scoped to managed departments
        $statsBaseQuery = ExitPermit::query();
        if (!$user->isAdmin() && !$user->isGa()) {
            $statsBaseQuery->whereIn('department', $managedDepts);
        }

        $totalActiveOutside = (clone $statsBaseQuery)->where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $totalOverdue = (clone $statsBaseQuery)->overdue()->count();
        $totalPending = (clone $statsBaseQuery)->where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $totalReturnedToday = (clone $statsBaseQuery)->where('status', ExitPermit::STATUS_RETURNED)
            ->whereDate('actual_return_at', Carbon::now('Asia/Jakarta')->toDateString())
            ->count();

        return view('sik.department.index', compact(
            'permits',
            'managedDepts',
            'totalActiveOutside',
            'totalOverdue',
            'totalPending',
            'totalReturnedToday'
        ));
    }

    /**
     * Display department historical SIK archive.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $managedDepts = $this->departmentApprovalService->getManagedDepartments($user);

        if (!$user->isAdmin() && !$user->isGa() && empty($managedDepts)) {
            abort(403, 'Anda tidak memiliki kewenangan melihat riwayat SIK departemen.');
        }

        if (($user->isAdmin() || $user->isGa()) && empty($managedDepts)) {
            $managedDepts = ExitPermit::select('department')->distinct()->pluck('department')->filter()->values()->toArray();
        }

        $query = ExitPermit::with(['user', 'approver', 'exitScannedBy', 'returnScannedBy', 'closedBy', 'revokedBy'])
            ->whereIn('status', [
                ExitPermit::STATUS_RETURNED,
                ExitPermit::STATUS_CLOSED_MANUALLY,
                ExitPermit::STATUS_REVOKED,
                ExitPermit::STATUS_REJECTED,
            ])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin() && !$user->isGa()) {
            $query->whereIn('department', $managedDepts);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sik_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Department Filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $historyPermits = $query->paginate(15)->withQueryString();

        return view('sik.department.history', compact('historyPermits', 'managedDepts'));
    }
}
