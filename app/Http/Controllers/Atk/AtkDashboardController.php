<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use App\Models\AtkRequest;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;

class AtkDashboardController extends Controller
{
    /**
     * Enforce Admin/GA manager access.
     */
    protected function authorizeManager()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }
        if ($user->isGa() && $user->hasModuleAccess('atk')) {
            return;
        }
        abort(403, 'Akses ditolak. Diperlukan hak akses Manajemen ATK.');
    }

    /**
     * Display the ATK Dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizeManager();

        $periodService = app(\App\Services\DashboardPeriodService::class);
        $periodInfo = $periodService->parsePeriod($request, 'period', 'date', 'monthly');
        $start = $periodInfo['start_date'];
        $end = $periodInfo['end_date'];

        // 1. Snapshot Master Stok (All-Time Warehouse Inventory Status)
        $totalItems = AtkItem::count();
        $totalVariants = AtkVariant::count();
        $availableStock = AtkVariant::where('is_active', true)->sum('stock');
        $lowStockCount = AtkVariant::where('is_active', true)
            ->whereRaw('stock <= minimum_stock')
            ->where('stock', '>', 0)
            ->count();
        $outOfStockCount = AtkVariant::where('is_active', true)
            ->where('stock', '=', 0)
            ->count();

        // 2. Metrik Permintaan Berbasis Periode Aktif
        $periodRequestQuery = AtkRequest::whereBetween('created_at', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);

        $pendingCount = (clone $periodRequestQuery)->where('status', 'diajukan')->count();
        $totalPendingBacklog = AtkRequest::where('status', 'diajukan')->count();
        $approvedCount = (clone $periodRequestQuery)->where('status', 'disetujui')->count();
        $processingCount = (clone $periodRequestQuery)->where('status', 'diproses')->count();
        $completedCount = (clone $periodRequestQuery)->where('status', 'selesai')->count();
        $rejectedCount = (clone $periodRequestQuery)->where('status', 'ditolak')->count();
        $totalRequestsInPeriod = (clone $periodRequestQuery)->count();

        // 3. Lists
        $recentRequests = (clone $periodRequestQuery)->with(['user', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        if ($recentRequests->isEmpty()) {
            $recentRequests = AtkRequest::with(['user', 'items.variant'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        $lowStockVariants = AtkVariant::with('item')
            ->where('is_active', true)
            ->whereRaw('stock <= minimum_stock')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        $periodMovementQuery = AtkStockMovement::whereBetween('created_at', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
        $recentMovements = (clone $periodMovementQuery)->with(['variant.item', 'actor'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        if ($recentMovements->isEmpty()) {
            $recentMovements = AtkStockMovement::with(['variant.item', 'actor'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('atk.dashboard', compact(
            'periodInfo',
            'totalItems',
            'totalVariants',
            'availableStock',
            'lowStockCount',
            'outOfStockCount',
            'pendingCount',
            'totalPendingBacklog',
            'approvedCount',
            'processingCount',
            'completedCount',
            'rejectedCount',
            'totalRequestsInPeriod',
            'recentRequests',
            'lowStockVariants',
            'recentMovements'
        ));
    }
}
