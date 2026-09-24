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

        if ($user->isAdmin() || $user->isSpv() || $user->isGa()) {
            // Support period filtering for Admin/SPV/GA
            $dateFilter = $request->input('date_filter', 'this_month');
            $start = null;
            $end = null;
            $now = \Carbon\Carbon::now('Asia/Jakarta');

            if ($dateFilter === 'today' || $dateFilter === 'daily') {
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'weekly') {
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'monthly') {
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
            } elseif ($dateFilter === 'this_year' || $dateFilter === 'yearly') {
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay();
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay();
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

            // Trend Data based on granularity
            $trendLabels = [];
            $trendValues = [];

            if ($dateFilter === 'today' || $dateFilter === 'daily') {
                $inspectionsForTrend = Inspection::whereBetween('created_at', [
                    $now->copy()->startOfDay(),
                    $now->copy()->endOfDay()
                ])->get();
                
                for ($h = 7; $h <= 20; $h++) {
                    $lbl = sprintf('%02d:00', $h);
                    $trendLabels[] = $lbl;
                    $trendValues[] = 0;
                }
                foreach ($inspectionsForTrend as $ins) {
                    $hour = (int) $ins->created_at->setTimezone('Asia/Jakarta')->format('G');
                    if ($hour >= 7 && $hour <= 20) {
                        $idx = $hour - 7;
                        $trendValues[$idx]++;
                    }
                }
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'weekly') {
                $inspectionsForTrend = Inspection::whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ])->get();

                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $dayCounts = array_fill_keys($days, 0);
                foreach ($inspectionsForTrend as $ins) {
                    $dayName = $ins->created_at->setTimezone('Asia/Jakarta')->format('l');
                    if (isset($dayCounts[$dayName])) {
                        $dayCounts[$dayName]++;
                    }
                }
                $trendLabels = $days;
                $trendValues = array_values($dayCounts);
            } elseif ($dateFilter === 'this_year' || $dateFilter === 'yearly') {
                $currentYearNum = (int)$now->format('Y');
                $years = [$currentYearNum - 2, $currentYearNum - 1, $currentYearNum];
                $yearCounts = array_fill_keys($years, 0);

                $inspectionsForTrend = Inspection::whereYear('created_at', '>=', $currentYearNum - 2)->get();
                foreach ($inspectionsForTrend as $ins) {
                    $yr = (int)$ins->created_at->setTimezone('Asia/Jakarta')->format('Y');
                    if (isset($yearCounts[$yr])) {
                        $yearCounts[$yr]++;
                    }
                }
                foreach ($years as $yr) {
                    $trendLabels[] = (string)$yr;
                    $trendValues[] = $yearCounts[$yr];
                }
            } elseif ($dateFilter === 'custom') {
                $inspectionsForTrend = [];
                if ($start && $end) {
                    $inspectionsForTrend = Inspection::whereBetween('created_at', [$start, $end])
                        ->orderBy('created_at', 'asc')
                        ->get();
                }
                $dateCounts = [];
                foreach ($inspectionsForTrend as $ins) {
                    $dateStr = $ins->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d');
                    if (!isset($dateCounts[$dateStr])) {
                        $dateCounts[$dateStr] = 0;
                    }
                    $dateCounts[$dateStr]++;
                }
                $trendLabels = array_keys($dateCounts);
                $trendValues = array_values($dateCounts);
            } else {
                // Default to Monthly (this_month / monthly) - display months of current year
                $inspectionsForTrend = Inspection::whereBetween('created_at', [
                    $now->copy()->startOfYear()->setTimezone('UTC'),
                    $now->copy()->endOfYear()->setTimezone('UTC')
                ])->get();

                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $monthCounts = array_fill_keys(range(1, 12), 0);
                foreach ($inspectionsForTrend as $ins) {
                    $mNum = (int) $ins->created_at->setTimezone('Asia/Jakarta')->format('n');
                    if (isset($monthCounts[$mNum])) {
                        $monthCounts[$mNum]++;
                    }
                }
                $trendLabels = $months;
                $trendValues = array_values($monthCounts);
            }

            // Combine labels and values back to trendData for chart compatibility
            $trendData = array_combine($trendLabels, $trendValues);

            // Pending inspections list for SPV review
            $pendingInspections = [];
            if ($user->isSpv()) {
                $pendingInspections = Inspection::with(['location', 'inspector'])
                    ->where('status', 'submitted')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Recent inspections for Admin/GA
            $recentInspections = [];
            if ($user->isAdmin() || $user->isGa()) {
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
                'recentInspections',
                'dateFilter'
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

        // 1. Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 2. Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        // 3. Floor filter
        if ($request->filled('floor')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('floor', $request->input('floor'));
            });
        }



        // 5. Inspector filter
        if ($request->filled('inspector_id')) {
            $query->where('inspector_id', $request->input('inspector_id'));
        }

        // 6. Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('inspector', function($qi) use ($search) {
                      $qi->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('location', function($ql) use ($search) {
                      $ql->where('building', 'like', "%{$search}%")
                         ->orWhere('toilet_name', 'like', "%{$search}%");
                  });
            });
        }

        // 7. Period/Date range filter
        $dateFilter = $request->input('date_filter');
        $start = null;
        $end = null;
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        if ($dateFilter) {
            if ($dateFilter === 'today' || $dateFilter === 'daily') {
                $start = $now->copy()->startOfDay()->setTimezone('UTC');
                $end = $now->copy()->endOfDay()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'weekly') {
                $start = $now->copy()->startOfWeek()->setTimezone('UTC');
                $end = $now->copy()->endOfWeek()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'monthly') {
                $start = $now->copy()->startOfMonth()->setTimezone('UTC');
                $end = $now->copy()->endOfMonth()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_year' || $dateFilter === 'yearly') {
                $start = $now->copy()->startOfYear()->setTimezone('UTC');
                $end = $now->copy()->endOfYear()->setTimezone('UTC');
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                }
            }
            if ($start && $end) {
                $query->whereBetween('inspections.inspected_at', [$start, $end]);
            }
        }

        $inspections = $query->paginate(10)->withQueryString();
        $locations = \App\Models\ToiletLocation::orderBy('building')->get();
        $inspectors = \App\Models\User::where('role', 'user')
            ->whereHas('modules', function($q) {
                $q->where('module', 'toilet_inspection');
            })
            ->orderBy('name')
            ->get();

        return view('admin.toilet_inspection.inspections.index', compact('inspections', 'locations', 'inspectors', 'dateFilter'));
    }

    /**
     * Display a list of all approvals/reviews for Admin.
     */
    public function approvals(Request $request)
    {
        $query = Inspection::with(['location', 'inspector', 'approval.approver'])
            ->where('status', '!=', 'draft');

        // 1. Approval Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'pending') {
                $query->where('status', 'submitted');
            } else {
                $query->where('status', $status);
            }
        }

        // 2. Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        // 3. Floor filter
        if ($request->filled('floor')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('floor', $request->input('floor'));
            });
        }

        // 4. Inspector filter
        if ($request->filled('inspector_id')) {
            $query->where('inspector_id', $request->input('inspector_id'));
        }

        // 5. Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('inspector', function($qi) use ($search) {
                      $qi->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('location', function($ql) use ($search) {
                      $ql->where('building', 'like', "%{$search}%")
                         ->orWhere('toilet_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('approval.approver', function($qa) use ($search) {
                      $qa->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 6. Period/Date range filter
        $dateFilter = $request->input('date_filter');
        $start = null;
        $end = null;
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        if ($dateFilter) {
            if ($dateFilter === 'today' || $dateFilter === 'daily') {
                $start = $now->copy()->startOfDay()->setTimezone('UTC');
                $end = $now->copy()->endOfDay()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'weekly') {
                $start = $now->copy()->startOfWeek()->setTimezone('UTC');
                $end = $now->copy()->endOfWeek()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'monthly') {
                $start = $now->copy()->startOfMonth()->setTimezone('UTC');
                $end = $now->copy()->endOfMonth()->setTimezone('UTC');
            } elseif ($dateFilter === 'this_year' || $dateFilter === 'yearly') {
                $start = $now->copy()->startOfYear()->setTimezone('UTC');
                $end = $now->copy()->endOfYear()->setTimezone('UTC');
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                }
            }
            if ($start && $end) {
                $query->where(function($q) use ($start, $end) {
                    $q->whereBetween('inspections.inspected_at', [$start, $end])
                      ->orWhereHas('approval', function($qa) use ($start, $end) {
                          $qa->whereBetween('actioned_at', [$start, $end]);
                      });
                });
            }
        }

        $inspections = $query->orderBy('inspected_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $locations = \App\Models\ToiletLocation::orderBy('building')->get();
        $inspectors = \App\Models\User::where('role', 'user')
            ->whereHas('modules', function($q) {
                $q->where('module', 'toilet_inspection');
            })
            ->orderBy('name')
            ->get();

        return view('admin.toilet_inspection.approvals.index', compact('inspections', 'locations', 'inspectors', 'dateFilter'));
    }
}
