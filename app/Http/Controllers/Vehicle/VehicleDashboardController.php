<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use App\Models\VehicleRequest;
use App\Services\DashboardPeriodService;
use App\Services\VehicleRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VehicleDashboardController extends Controller
{
    public function __construct(
        protected VehicleRequestService $vehicleRequestService,
        protected DashboardPeriodService $periodService
    ) {}

    /**
     * Display the Vehicle Operational Dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Operational overview is intended for GA and Admin; regular users are redirected to their requests list
        if (!$user->isAdmin() && !$user->isGa()) {
            return redirect()->route('vehicles.requests.index');
        }

        $now = Carbon::now('Asia/Jakarta');

        // Automatically complete approved requests whose end_datetime has elapsed
        $this->vehicleRequestService->autoCompleteExpiredRequests($now);

        // Standard period resolution
        $periodInfo = $this->periodService->parsePeriod($request, 'period', 'date', 'monthly');
        $start = $periodInfo['start_date'];
        $end = $periodInfo['end_date'];
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');

        // =========================================================================
        // 1. ACTIVE TRIPS (CURRENTLY IN USE RIGHT NOW)
        // Strictly: status = 'approved' AND start_datetime <= now AND end_datetime > now
        // =========================================================================
        $activeTrips = VehicleRequest::with(['user', 'vehicle', 'driver'])
            ->where('status', 'approved')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>', $now)
            ->orderBy('start_datetime')
            ->get();

        $inUseVehicleIds = $activeTrips->pluck('vehicle_id')->filter()->unique()->toArray();
        $inUseDriverIds = $activeTrips->pluck('driver_id')->filter()->unique()->toArray();

        // =========================================================================
        // 2. FLEET / ARMADA SNAPSHOT (DERIVED AVAILABILITY)
        // =========================================================================
        $totalVehicles = Vehicle::count();
        $maintenanceVehicles = Vehicle::where('status', 'maintenance')->count();
        $inactiveVehicles = Vehicle::where('status', 'inactive')->count();
        $inUseVehiclesCount = count($inUseVehicleIds);
        // Available: master status is 'available' and NOT currently in use right now
        $availableVehicles = Vehicle::where('status', 'available')
            ->whereNotIn('id', $inUseVehicleIds)
            ->count();

        // =========================================================================
        // 3. DRIVERS SNAPSHOT (DERIVED ON DUTY)
        // =========================================================================
        $totalDrivers = Driver::count();
        $inactiveDrivers = Driver::where('status', 'inactive')->count();
        $onDutyDriversCount = count($inUseDriverIds);
        // Available: master status is 'available' and NOT currently on duty right now
        $availableDrivers = Driver::where('status', 'available')
            ->whereNotIn('id', $inUseDriverIds)
            ->count();

        // =========================================================================
        // 4. REQUESTS STATISTIC IN SELECTED PERIOD
        // =========================================================================
        $periodRequestsQuery = VehicleRequest::whereBetween('start_datetime', [$startStr, $endStr]);

        $totalRequestsInPeriod = (clone $periodRequestsQuery)->count();
        $pendingRequestsInPeriod = (clone $periodRequestsQuery)->where('status', 'pending_approval')->count();
        $totalPendingBacklog = VehicleRequest::where('status', 'pending_approval')->count();
        // "Disetujui/Berjalan" in period (statistic based on active period, distinct from "Sedang Digunakan Saat Ini"):
        $approvedInPeriod = (clone $periodRequestsQuery)->where('status', 'approved')->count();
        $completedInPeriod = (clone $periodRequestsQuery)->where('status', 'completed')->count();
        $rejectedInPeriod = (clone $periodRequestsQuery)->where('status', 'rejected')->count();
        $cancelledInPeriod = (clone $periodRequestsQuery)->where('status', 'cancelled')->count();

        // =========================================================================
        // 5. FUEL CONSUMPTION (BBM) IN SELECTED PERIOD
        // =========================================================================
        $periodFuelQuery = VehicleFuelLog::whereBetween('recorded_at', [$startStr, $endStr]);
        $totalFuelLiters = (float)(clone $periodFuelQuery)->sum('fuel_liters');
        $totalFuelCost = (float)(clone $periodFuelQuery)->sum('fuel_cost');

        // Average km/L calculation in period without N+1 queries
        $fuelLogsWithPrev = VehicleFuelLog::from('vehicle_fuel_logs')
            ->whereBetween('recorded_at', [$startStr, $endStr])
            ->addSelect([
                'vehicle_fuel_logs.*',
                'previous_odometer' => VehicleFuelLog::from('vehicle_fuel_logs as prev_log')
                    ->select('prev_log.odometer')
                    ->whereColumn('prev_log.vehicle_id', 'vehicle_fuel_logs.vehicle_id')
                    ->where(function ($q) {
                        $q->whereColumn('prev_log.recorded_at', '<', 'vehicle_fuel_logs.recorded_at')
                          ->orWhere(function ($sub) {
                              $sub->whereColumn('prev_log.recorded_at', 'vehicle_fuel_logs.recorded_at')
                                  ->whereColumn('prev_log.id', '<', 'vehicle_fuel_logs.id');
                          });
                    })
                    ->orderByDesc('prev_log.recorded_at')
                    ->orderByDesc('prev_log.id')
                    ->limit(1)
            ])
            ->get();

        $totalDistance = 0;
        $totalLitersForDistance = 0;
        foreach ($fuelLogsWithPrev as $log) {
            if ($log->previous_odometer !== null && $log->odometer > $log->previous_odometer && $log->fuel_liters > 0) {
                $totalDistance += ($log->odometer - $log->previous_odometer);
                $totalLitersForDistance += (float)$log->fuel_liters;
            }
        }
        $avgKmLiter = $totalLitersForDistance > 0 ? round($totalDistance / $totalLitersForDistance, 2) : null;

        // =========================================================================
        // 6. UPCOMING SCHEDULES IN PERIOD & RECENT REQUESTS
        // =========================================================================
        $upcomingTrips = VehicleRequest::with(['user', 'vehicle', 'driver'])
            ->where('status', 'approved')
            ->where('start_datetime', '>', $now)
            ->where('start_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->take(5)
            ->get();

        $recentRequests = (clone $periodRequestsQuery)->with(['user', 'vehicle', 'driver'])
            ->orderBy('start_datetime', 'desc')
            ->take(6)
            ->get();
        if ($recentRequests->isEmpty()) {
            $recentRequests = VehicleRequest::with(['user', 'vehicle', 'driver'])
                ->orderBy('start_datetime', 'desc')
                ->take(6)
                ->get();
        }

        return view('vehicles.dashboard', compact(
            'periodInfo',
            'activeTrips',
            'totalVehicles',
            'availableVehicles',
            'inUseVehiclesCount',
            'maintenanceVehicles',
            'inactiveVehicles',
            'totalDrivers',
            'availableDrivers',
            'onDutyDriversCount',
            'inactiveDrivers',
            'totalRequestsInPeriod',
            'pendingRequestsInPeriod',
            'totalPendingBacklog',
            'approvedInPeriod',
            'completedInPeriod',
            'rejectedInPeriod',
            'cancelledInPeriod',
            'totalFuelLiters',
            'totalFuelCost',
            'avgKmLiter',
            'upcomingTrips',
            'recentRequests'
        ));
    }
}
