<?php

namespace App\Http\Controllers\ToiletInspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Illuminate\Http\Request;

class InspectionDashboardController extends Controller
{
    /**
     * Display the Toilet Inspection dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isSpv()) {
            // Support period filtering for Admin/SPV
            $start = null;
            $end = null;
            $dateFilter = $request->input('date_filter', 'this_month');

            if ($dateFilter === 'today') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfDay()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_week') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfWeek()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfWeek()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_month') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfMonth()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfMonth()->setTimezone('UTC');
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                }
            }

            $baseQuery = Inspection::query();
            if ($start && $end) {
                $baseQuery->whereBetween('inspections.created_at', [$start, $end]);
            }

            $totalInspections = (clone $baseQuery)->count();
            $pendingCount = (clone $baseQuery)->where('status', 'submitted')->count();
            $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
            $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

            $approvalRate = 0.0;
            if ($totalInspections > 0) {
                $approvalRate = round(($approvedCount / $totalInspections) * 100, 1);
            }

            // Location Summary
            $locationSummary = (clone $baseQuery)
                ->join('toilet_locations', 'inspections.location_id', '=', 'toilet_locations.id')
                ->selectRaw('toilet_locations.building, toilet_locations.floor, 
                             count(inspections.id) as total, 
                             sum(case when inspections.status = "approved" then 1 else 0 end) as approved,
                             sum(case when inspections.status = "rejected" then 1 else 0 end) as rejected')
                ->groupBy('toilet_locations.building', 'toilet_locations.floor')
                ->orderBy('toilet_locations.building')
                ->orderBy('toilet_locations.floor')
                ->get();

            // Trend Data
            $inspectionsForTrend = (clone $baseQuery)
                ->select('created_at')
                ->orderBy('created_at', 'asc')
                ->get();

            $trendData = [];
            foreach ($inspectionsForTrend as $ins) {
                $dateStr = $ins->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d');
                if (!isset($trendData[$dateStr])) {
                    $trendData[$dateStr] = 0;
                }
                $trendData[$dateStr]++;
            }

            // Pending inspections list for SPV review
            $pendingInspections = [];
            if ($user->isSpv()) {
                $pendingInspections = Inspection::with(['location', 'inspector'])
                    ->where('status', 'submitted')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Recent inspections for Admin
            $recentInspections = [];
            if ($user->isAdmin()) {
                $recentInspections = Inspection::with(['location', 'inspector'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            return view('toilet_inspection.dashboard', compact(
                'totalInspections',
                'pendingCount',
                'approvedCount',
                'rejectedCount',
                'approvalRate',
                'locationSummary',
                'trendData',
                'pendingInspections',
                'recentInspections'
            ));
        }

        // Inspector/User stats & history list (does NOT receive period selector/charts)
        $totalInspections = Inspection::where('inspector_id', $user->id)->count();
        $pendingCount = Inspection::where('inspector_id', $user->id)->where('status', 'submitted')->count();
        $approvedCount = Inspection::where('inspector_id', $user->id)->where('status', 'approved')->count();
        $rejectedCount = Inspection::where('inspector_id', $user->id)->where('status', 'rejected')->count();

        $myInspections = Inspection::with('location')
            ->where('inspector_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('toilet_inspection.dashboard', compact(
            'totalInspections',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'myInspections'
        ));
    }

    /**
     * Display a list of all inspections for Admin.
     */
    public function inspections(Request $request)
    {
        $query = Inspection::with(['location', 'inspector'])->orderBy('inspected_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        $inspections = $query->paginate(10)->withQueryString();
        $locations = \App\Models\ToiletLocation::orderBy('building')->get();

        return view('admin.toilet_inspection.inspections.index', compact('inspections', 'locations'));
    }

    /**
     * Display a list of all approvals/reviews for Admin.
     */
    public function approvals(Request $request)
    {
        $query = \App\Models\InspectionApproval::with(['inspection.location', 'inspection.inspector', 'approver'])
            ->orderBy('actioned_at', 'desc');

        $approvals = $query->paginate(10)->withQueryString();

        return view('admin.toilet_inspection.approvals.index', compact('approvals'));
    }
}
