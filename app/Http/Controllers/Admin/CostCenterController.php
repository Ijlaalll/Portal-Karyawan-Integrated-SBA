<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\CostCenterTransaction;
use App\Models\Room;
use App\Services\CostCenterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostCenterController extends Controller
{
    protected CostCenterService $costCenterService;

    public function __construct(CostCenterService $costCenterService)
    {
        $this->costCenterService = $costCenterService;
    }

    /**
     * Display the Cost Center Overview Dashboard.
     */
    public function overview(Request $request)
    {
        $now = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Core Summary Metrics
        $totalExpenditure = (float) CostCenterTransaction::sum('total_amount');
        $roomExpenditure = (float) CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')->sum('total_amount');
        $atkExpenditure = (float) CostCenterTransaction::where('activity_type', 'ATK_USAGE')->sum('total_amount');
        $totalTransactionsCount = CostCenterTransaction::count();
        $activeCostCentersCount = CostCenter::where('status', 'active')->count();

        // 2. Month-over-Month Comparisons
        $thisMonthSpend = (float) CostCenterTransaction::where('created_at', '>=', $startThisMonth)->sum('total_amount');
        $lastMonthSpend = (float) CostCenterTransaction::whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('total_amount');
        $momPercentage = $lastMonthSpend > 0 
            ? round((($thisMonthSpend - $lastMonthSpend) / $lastMonthSpend) * 100, 1)
            : 0;

        // 3. Top Cost Centers by Usage
        $topCostCenters = CostCenter::withSum('transactions', 'total_amount')
            ->orderByDesc('transactions_sum_total_amount')
            ->take(5)
            ->get();

        // 4. Recent Transactions
        $recentTransactions = CostCenterTransaction::with(['costCenter', 'user'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.settings.cost_centers.overview', compact(
            'totalExpenditure',
            'roomExpenditure',
            'atkExpenditure',
            'totalTransactionsCount',
            'activeCostCentersCount',
            'thisMonthSpend',
            'lastMonthSpend',
            'momPercentage',
            'topCostCenters',
            'recentTransactions'
        ));
    }

    /**
     * Display a listing of cost centers (Cost Centers tab).
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $costCenters = $this->costCenterService->getPaginated($search);

        $totalCostCenters = CostCenter::count();
        $activeCount = CostCenter::where('status', 'active')->count();
        $inactiveCount = CostCenter::where('status', 'inactive')->count();

        return view('admin.settings.cost_centers.index', compact(
            'costCenters', 
            'search',
            'totalCostCenters',
            'activeCount',
            'inactiveCount'
        ));
    }

    /**
     * Show the form for creating a new cost center.
     */
    public function create()
    {
        return view('admin.settings.cost_centers.create');
    }

    /**
     * Store a newly created cost center in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cost_center_code' => ['required', 'string', 'max:50', 'unique:cost_centers,cost_center_code'],
            'name'             => ['nullable', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:255'],
            'person_responsible' => ['required', 'string', 'max:255'],
            'company_code'     => ['required', 'string', 'max:50'],
            'status'           => ['nullable', 'string', 'in:active,inactive'],
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = $validated['description'];
        }
        $validated['status'] = $validated['status'] ?? 'active';

        $this->costCenterService->create($validated);

        return redirect()->route('admin.settings.cost-centers.index')
            ->with('success', 'Cost Center berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified cost center.
     */
    public function edit(CostCenter $costCenter)
    {
        return view('admin.settings.cost_centers.edit', compact('costCenter'));
    }

    /**
     * Update the specified cost center in storage.
     */
    public function update(Request $request, CostCenter $costCenter)
    {
        $validated = $request->validate([
            'cost_center_code' => ['required', 'string', 'max:50', 'unique:cost_centers,cost_center_code,' . $costCenter->id],
            'name'             => ['nullable', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:255'],
            'person_responsible' => ['required', 'string', 'max:255'],
            'company_code'     => ['required', 'string', 'max:50'],
            'status'           => ['nullable', 'string', 'in:active,inactive'],
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = $validated['description'];
        }
        $validated['status'] = $validated['status'] ?? ($costCenter->status ?? 'active');

        $this->costCenterService->update($costCenter, $validated);

        return redirect()->route('admin.settings.cost-centers.index')
            ->with('success', 'Cost Center berhasil diperbarui.');
    }

    /**
     * Remove the specified cost center from storage.
     */
    public function destroy(CostCenter $costCenter)
    {
        try {
            $this->costCenterService->delete($costCenter);

            return redirect()->route('admin.settings.cost-centers.index')
                ->with('success', 'Cost Center berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.cost-centers.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the Room Pricing configuration section.
     */
    public function pricing(Request $request)
    {
        $rooms = Room::orderBy('name', 'asc')->get();

        return view('admin.settings.cost_centers.pricing', compact('rooms'));
    }

    /**
     * Update room rate and pricing status.
     */
    public function updatePricing(Request $request, Room $room)
    {
        $validated = $request->validate([
            'price_per_hour'    => ['required', 'numeric', 'min:0'],
            'is_pricing_active' => ['required', 'boolean'],
        ]);

        $room->update([
            'price_per_hour'    => $validated['price_per_hour'],
            'is_pricing_active' => $validated['is_pricing_active'],
        ]);

        return redirect()->route('admin.settings.cost-centers.pricing')
            ->with('success', "Tarif untuk ruangan {$room->name} berhasil diperbarui.");
    }

    /**
     * Display the Activities & Transactions listing.
     */
    public function transactions(Request $request)
    {
        $query = CostCenterTransaction::with(['costCenter', 'user']);

        // Filter by Activity Type
        if ($request->filled('activity_type') && $request->activity_type !== 'all') {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by Cost Center
        if ($request->filled('cost_center_id') && $request->cost_center_id !== 'all') {
            $query->where('cost_center_id', $request->cost_center_id);
        }

        // Filter by Status (posted, reversed)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in description, item name, or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $costCenters = CostCenter::orderBy('name', 'asc')->get();

        return view('admin.settings.cost_centers.transactions', compact('transactions', 'costCenters'));
    }

    /**
     * Display filtered Reports & Analytics.
     */
    public function reports(Request $request)
    {
        $costCenters = CostCenter::orderBy('name', 'asc')->get();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $selectedCostCenterId = $request->input('cost_center_id', 'all');

        $baseQuery = CostCenterTransaction::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($selectedCostCenterId !== 'all') {
            $baseQuery->where('cost_center_id', $selectedCostCenterId);
        }

        $totalSpend = (float) (clone $baseQuery)->sum('total_amount');
        $roomSpend = (float) (clone $baseQuery)->where('activity_type', 'ROOM_BOOKING')->sum('total_amount');
        $atkSpend = (float) (clone $baseQuery)->where('activity_type', 'ATK_USAGE')->sum('total_amount');
        $txCount = (clone $baseQuery)->count();

        // Breakdown by Cost Center
        $costCenterBreakdown = (clone $baseQuery)
            ->select('cost_center_id', DB::raw('SUM(total_amount) as total_spent'), DB::raw('COUNT(*) as tx_count'))
            ->groupBy('cost_center_id')
            ->with('costCenter')
            ->orderByDesc('total_spent')
            ->get();

        // Breakdown by Item / Room
        $itemBreakdown = (clone $baseQuery)
            ->select('item_name', 'activity_type', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('item_name', 'activity_type')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        return view('admin.settings.cost_centers.reports', compact(
            'costCenters',
            'startDate',
            'endDate',
            'selectedCostCenterId',
            'totalSpend',
            'roomSpend',
            'atkSpend',
            'txCount',
            'costCenterBreakdown',
            'itemBreakdown'
        ));
    }
}
