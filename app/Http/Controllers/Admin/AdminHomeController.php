<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\ToiletLocation;
use App\Models\Inspection;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use App\Models\AtkRequest;
use App\Models\ExitPermit;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Services\DashboardPeriodService;
use App\Services\VehicleRequestService;

class AdminHomeController extends Controller
{
    public function __construct(
        protected DashboardPeriodService $periodService,
        protected VehicleRequestService $vehicleRequestService
    ) {}

    /**
     * Display the Global Admin Dashboard.
     */
    public function index(Request $request)
    {
        Booking::syncCompletedBookings();
        // Clear active module context when on the Global Dashboard
        session()->forget('active_admin_module');

        $now = Carbon::now('Asia/Jakarta');

        // ==========================================
        // 1. MEETING ROOM OVERVIEW FILTERS & QUERIES
        // ==========================================
        $mrPeriod = $request->input('mr_period', 'monthly');
        $mrDateInput = $request->input('mr_date');

        if (empty($mrDateInput)) {
            if ($mrPeriod === 'yearly') {
                $mrDateInput = $now->format('Y');
            } elseif ($mrPeriod === 'monthly') {
                $mrDateInput = $now->format('Y-m');
            } else {
                $mrDateInput = $now->format('Y-m-d');
            }
        }

        try {
            if ($mrPeriod === 'yearly') {
                $date = Carbon::createFromFormat('Y-m-d', $mrDateInput . '-01-01', 'Asia/Jakarta');
                $mrStart = $date->copy()->startOfYear();
                $mrEnd = $date->copy()->endOfYear();
            } elseif ($mrPeriod === 'monthly') {
                $date = Carbon::createFromFormat('Y-m-d', $mrDateInput . '-01', 'Asia/Jakarta');
                $mrStart = $date->copy()->startOfMonth();
                $mrEnd = $date->copy()->endOfMonth();
            } else {
                $date = Carbon::createFromFormat('Y-m-d', $mrDateInput, 'Asia/Jakarta');
                if ($mrPeriod === 'weekly') {
                    $mrStart = $date->copy()->startOfWeek();
                    $mrEnd = $date->copy()->endOfWeek();
                } else { // daily
                    $mrStart = $date->copy()->startOfDay();
                    $mrEnd = $date->copy()->endOfDay();
                }
            }
        } catch (\Exception $e) {
            $mrStart = $now->copy()->startOfMonth();
            $mrEnd = $now->copy()->endOfMonth();
            $mrPeriod = 'monthly';
            $mrDateInput = $now->format('Y-m');
        }

        $mrBookings = Booking::with('room')
            ->whereBetween('booking_date', [$mrStart->format('Y-m-d'), $mrEnd->format('Y-m-d')])
            ->get();

        $mrTotalBookings = $mrBookings->count();

        // Calculate usage per room
        $mrRoomCounts = [];
        $allRooms = Room::all();
        foreach ($allRooms as $r) {
            $mrRoomCounts[$r->name] = 0;
        }
        foreach ($mrBookings as $b) {
            if ($b->room) {
                $mrRoomCounts[$b->room->name]++;
            }
        }
        arsort($mrRoomCounts);

        $mrMostUsedRoom = 'N/A';
        $mrMostUsedCount = 0;
        if (!empty($mrRoomCounts) && max($mrRoomCounts) > 0) {
            $mrMostUsedRoom = array_key_first($mrRoomCounts);
            $mrMostUsedCount = max($mrRoomCounts);
        }

        // Generate trend data based on period
        $mrTrendLabels = [];
        $mrTrendData = [];

        if ($mrPeriod === 'daily') {
            for ($h = 8; $h <= 18; $h++) {
                $lbl = sprintf('%02d:00', $h);
                $mrTrendLabels[] = $lbl;
                $mrTrendData[$lbl] = 0;
            }
            foreach ($mrBookings as $b) {
                $startHour = (int) explode(':', $b->start_time)[0];
                if ($startHour >= 8 && $startHour <= 18) {
                    $lbl = sprintf('%02d:00', $startHour);
                    $mrTrendData[$lbl]++;
                }
            }
        } elseif ($mrPeriod === 'weekly') {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $dName) {
                $mrTrendLabels[] = $dName;
                $mrTrendData[$dName] = 0;
            }
            foreach ($mrBookings as $b) {
                $dayName = $b->booking_date->setTimezone('Asia/Jakarta')->format('l');
                if (isset($mrTrendData[$dayName])) {
                    $mrTrendData[$dayName]++;
                }
            }
        } elseif ($mrPeriod === 'yearly') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $idx => $mName) {
                $mrTrendLabels[] = $mName;
                $mrTrendData[$idx + 1] = 0;
            }
            foreach ($mrBookings as $b) {
                $monthNum = (int) $b->booking_date->setTimezone('Asia/Jakarta')->format('n');
                if (isset($mrTrendData[$monthNum])) {
                    $mrTrendData[$monthNum]++;
                }
            }
            $mrTrendData = array_values($mrTrendData);
        } else { // monthly
            $daysInMonth = $mrStart->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $mrTrendLabels[] = (string)$day;
                $mrTrendData[$day] = 0;
            }
            foreach ($mrBookings as $b) {
                $dayNum = (int) $b->booking_date->setTimezone('Asia/Jakarta')->format('j');
                if (isset($mrTrendData[$dayNum])) {
                    $mrTrendData[$dayNum]++;
                }
            }
            $mrTrendData = array_values($mrTrendData);
        }

        $mrReviewedBookings = $mrBookings->where('status', 'completed')->whereNotNull('rating');
        $mrTotalReviews = $mrReviewedBookings->count();
        $mrAverageRating = $mrReviewedBookings->count() > 0 ? round($mrReviewedBookings->avg('rating'), 1) : 0;

        $mrRoomPerformance = [];
        foreach ($allRooms as $room) {
            $roomBookings = $mrBookings->where('room_id', $room->id);
            $roomCompleted = $roomBookings->where('status', 'completed')->whereNotNull('rating');
            $avgRoomRating = $roomCompleted->count() > 0 ? round($roomCompleted->avg('rating'), 1) : null;
            
            $mrRoomPerformance[] = [
                'name' => $room->name,
                'location' => $room->location,
                'average_rating' => $avgRoomRating,
                'bookings_count' => $roomBookings->count(),
            ];
        }

        usort($mrRoomPerformance, function ($a, $b) {
            if ($a['average_rating'] === null && $b['average_rating'] !== null) return 1;
            if ($a['average_rating'] !== null && $b['average_rating'] === null) return -1;
            if ($a['average_rating'] === null && $b['average_rating'] === null) {
                return $b['bookings_count'] <=> $a['bookings_count'];
            }
            if ($a['average_rating'] != $b['average_rating']) {
                return $b['average_rating'] <=> $a['average_rating'];
            }
            return $b['bookings_count'] <=> $a['bookings_count'];
        });

        $top3Rooms = array_slice($mrRoomPerformance, 0, 3);

        $meetingRoomOverview = [
            'total_bookings' => $mrTotalBookings,
            'most_used_room' => $mrMostUsedRoom,
            'most_used_count' => $mrMostUsedCount,
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('status', 'active')->count(),
            'average_rating' => $mrAverageRating,
            'total_reviews' => $mrTotalReviews,
            'top_3_rooms' => $top3Rooms,
            'trend_labels' => $mrTrendLabels,
            'trend_data' => array_values($mrTrendData),
            'period' => $mrPeriod,
            'date_input' => $mrDateInput,
            'start_date' => $mrStart,
            'end_date' => $mrEnd,
        ];

        // =============================================
        // 2. TOILET INSPECTION OVERVIEW FILTERS & QUERIES
        // =============================================
        $tiPeriod = $request->input('ti_period', 'monthly');
        $tiDateInput = $request->input('ti_date');

        if (empty($tiDateInput)) {
            if ($tiPeriod === 'yearly') {
                $tiDateInput = $now->format('Y');
            } elseif ($tiPeriod === 'monthly') {
                $tiDateInput = $now->format('Y-m');
            } else {
                $tiDateInput = $now->format('Y-m-d');
            }
        }

        try {
            if ($tiPeriod === 'yearly') {
                $date = Carbon::createFromFormat('Y-m-d', $tiDateInput . '-01-01', 'Asia/Jakarta');
                $tiStart = $date->copy()->startOfYear()->setTimezone('UTC');
                $tiEnd = $date->copy()->endOfYear()->setTimezone('UTC');
            } elseif ($tiPeriod === 'monthly') {
                $date = Carbon::createFromFormat('Y-m-d', $tiDateInput . '-01', 'Asia/Jakarta');
                $tiStart = $date->copy()->startOfMonth()->setTimezone('UTC');
                $tiEnd = $date->copy()->endOfMonth()->setTimezone('UTC');
            } else {
                $date = Carbon::createFromFormat('Y-m-d', $tiDateInput, 'Asia/Jakarta');
                if ($tiPeriod === 'weekly') {
                    $tiStart = $date->copy()->startOfWeek()->setTimezone('UTC');
                    $tiEnd = $date->copy()->endOfWeek()->setTimezone('UTC');
                } else { // daily
                    $tiStart = $date->copy()->startOfDay()->setTimezone('UTC');
                    $tiEnd = $date->copy()->endOfDay()->setTimezone('UTC');
                }
            }
        } catch (\Exception $e) {
            $tiStart = $now->copy()->startOfMonth()->setTimezone('UTC');
            $tiEnd = $now->copy()->endOfMonth()->setTimezone('UTC');
            $tiPeriod = 'monthly';
            $tiDateInput = $now->format('Y-m');
        }

        $tiInspections = Inspection::whereBetween('created_at', [$tiStart, $tiEnd])->get();

        $tiTotalInspections = $tiInspections->count();
        $tiPending = $tiInspections->where('status', 'submitted')->count();
        $tiApproved = $tiInspections->where('status', 'approved')->count();
        $tiRejected = $tiInspections->where('status', 'rejected')->count();
        $tiCompleted = $tiApproved + $tiRejected;

        $tiApprovalRate = $tiCompleted > 0 ? round(($tiApproved / $tiCompleted) * 100, 1) : 0;

        // Generate trend data based on period
        $tiTrendLabels = [];
        $tiTrendData = [];

        if ($tiPeriod === 'daily') {
            for ($h = 8; $h <= 18; $h++) {
                $lbl = sprintf('%02d:00', $h);
                $tiTrendLabels[] = $lbl;
                $tiTrendData[$lbl] = 0;
            }
            foreach ($tiInspections as $ins) {
                $hour = (int) $ins->created_at->setTimezone('Asia/Jakarta')->format('G');
                if ($hour >= 8 && $hour <= 18) {
                    $lbl = sprintf('%02d:00', $hour);
                    $tiTrendData[$lbl]++;
                }
            }
        } elseif ($tiPeriod === 'weekly') {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $dName) {
                $tiTrendLabels[] = $dName;
                $tiTrendData[$dName] = 0;
            }
            foreach ($tiInspections as $ins) {
                $dayName = $ins->created_at->setTimezone('Asia/Jakarta')->format('l');
                if (isset($tiTrendData[$dayName])) {
                    $tiTrendData[$dayName]++;
                }
            }
        } elseif ($tiPeriod === 'yearly') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $idx => $mName) {
                $tiTrendLabels[] = $mName;
                $tiTrendData[$idx + 1] = 0;
            }
            foreach ($tiInspections as $ins) {
                $monthNum = (int) $ins->created_at->setTimezone('Asia/Jakarta')->format('n');
                if (isset($tiTrendData[$monthNum])) {
                    $tiTrendData[$monthNum]++;
                }
            }
            $tiTrendData = array_values($tiTrendData);
        } else { // monthly
            $startLocal = Carbon::parse($tiDateInput . '-01', 'Asia/Jakarta');
            $daysInMonth = $startLocal->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $tiTrendLabels[] = (string)$day;
                $tiTrendData[$day] = 0;
            }
            foreach ($tiInspections as $ins) {
                $dayNum = (int) $ins->created_at->setTimezone('Asia/Jakarta')->format('j');
                if (isset($tiTrendData[$dayNum])) {
                    $tiTrendData[$dayNum]++;
                }
            }
            $tiTrendData = array_values($tiTrendData);
        }

        $toiletInspectionOverview = [
            'total_inspections' => $tiTotalInspections,
            'completed' => $tiCompleted,
            'pending' => $tiPending,
            'approved' => $tiApproved,
            'rejected' => $tiRejected,
            'approval_rate' => $tiApprovalRate,
            'total_locations' => ToiletLocation::count(),
            'trend_labels' => $tiTrendLabels,
            'trend_data' => array_values($tiTrendData),
            'period' => $tiPeriod,
            'date_input' => $tiDateInput,
            'start_date' => Carbon::parse($tiStart)->setTimezone('Asia/Jakarta'),
            'end_date' => Carbon::parse($tiEnd)->setTimezone('Asia/Jakarta'),
        ];

        // ==========================================
        // 3. ATK OVERVIEW FILTERS & QUERIES
        // ==========================================
        $atkPeriodInfo = $this->periodService->parsePeriod($request, 'atk_period', 'atk_date', 'monthly');
        $atkStart = $atkPeriodInfo['start_date'];
        $atkEnd = $atkPeriodInfo['end_date'];
        $atkStartStr = $atkStart->format('Y-m-d H:i:s');
        $atkEndStr = $atkEnd->format('Y-m-d H:i:s');

        $atkPeriodRequestsQuery = AtkRequest::whereBetween('created_at', [$atkStartStr, $atkEndStr]);
        $atkPendingInPeriod = (clone $atkPeriodRequestsQuery)->where('status', 'diajukan')->count();
        $atkRecentRequests = (clone $atkPeriodRequestsQuery)->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        if ($atkRecentRequests->isEmpty()) {
            $atkRecentRequests = AtkRequest::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        $atkOverview = [
            'period' => $atkPeriodInfo['period'],
            'date_input' => $atkPeriodInfo['date_input'],
            'start_date' => $atkStart,
            'end_date' => $atkEnd,
            'total_items' => AtkItem::count(),
            'total_variants' => AtkVariant::count(),
            'pending_requests' => $atkPendingInPeriod,
            'low_stock' => AtkVariant::where('is_active', true)
                ->whereRaw('stock <= minimum_stock')
                ->where('stock', '>', 0)
                ->count(),
            'out_of_stock' => AtkVariant::where('is_active', true)
                ->where('stock', '<=', 0)
                ->count(),
            'recent_requests' => $atkRecentRequests,
        ];

        // ==========================================
        // 4. SURAT IZIN KELUAR (SIK) OVERVIEW FILTERS & QUERIES
        // ==========================================
        $sikPeriodInfo = $this->periodService->parsePeriod($request, 'sik_period', 'sik_date', 'monthly');
        $sikStart = $sikPeriodInfo['start_date'];
        $sikEnd = $sikPeriodInfo['end_date'];
        $sikStartDateStr = $sikStart->format('Y-m-d');
        $sikEndDateStr = $sikEnd->format('Y-m-d');

        $sikPeriodQuery = ExitPermit::whereDate('permit_date', '>=', $sikStartDateStr)
            ->whereDate('permit_date', '<=', $sikEndDateStr);

        $sikTotalPermits = (clone $sikPeriodQuery)->count();
        $sikPendingApproval = (clone $sikPeriodQuery)->where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $sikActiveOutside = (clone $sikPeriodQuery)->where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $sikOverdue = (clone $sikPeriodQuery)->overdue()->count();
        $sikReturnedInPeriod = (clone $sikPeriodQuery)->where('status', ExitPermit::STATUS_RETURNED)->count();

        $sikRecentRequests = (clone $sikPeriodQuery)->with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        if ($sikRecentRequests->isEmpty()) {
            $sikRecentRequests = ExitPermit::with(['user', 'approver'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        $sikOverview = [
            'period' => $sikPeriodInfo['period'],
            'date_input' => $sikPeriodInfo['date_input'],
            'start_date' => $sikStart,
            'end_date' => $sikEnd,
            'total_permits' => $sikTotalPermits,
            'pending_approval' => $sikPendingApproval,
            'active_outside' => $sikActiveOutside,
            'overdue' => $sikOverdue,
            'returned_today' => $sikReturnedInPeriod,
            'recent_requests' => $sikRecentRequests,
        ];

        // ==========================================
        // 5. VEHICLE USAGE OVERVIEW FILTERS & QUERIES
        // Focus strictly on vehicle usage (requests, status, active fleet)
        // No in-depth BBM, odometer, or driver operational details.
        // ==========================================
        $this->vehicleRequestService->autoCompleteExpiredRequests($now);

        $vhcPeriodInfo = $this->periodService->parsePeriod($request, 'vhc_period', 'vhc_date', 'monthly');
        $vhcStart = $vhcPeriodInfo['start_date'];
        $vhcEnd = $vhcPeriodInfo['end_date'];
        $vhcStartStr = $vhcStart->format('Y-m-d H:i:s');
        $vhcEndStr = $vhcEnd->format('Y-m-d H:i:s');

        // Strictly: "Sedang Digunakan" is approved request where start_datetime <= now < end_datetime
        $nowInUseRequests = VehicleRequest::where('status', 'approved')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>', $now)
            ->get();
        $nowInUseCount = $nowInUseRequests->count();
        $inUseVehicleIds = $nowInUseRequests->pluck('vehicle_id')->filter()->unique()->toArray();

        $availableVehiclesCount = Vehicle::where('status', 'available')
            ->whereNotIn('id', $inUseVehicleIds)
            ->count();
        $totalVehiclesCount = Vehicle::count();

        // Requests within active period
        $vhcPeriodQuery = VehicleRequest::whereBetween('start_datetime', [$vhcStartStr, $vhcEndStr]);
        $vhcTotalRequests = (clone $vhcPeriodQuery)->count();
        $vhcApproved = (clone $vhcPeriodQuery)->where('status', 'approved')->count();
        $vhcCompleted = (clone $vhcPeriodQuery)->where('status', 'completed')->count();
        $vhcPending = (clone $vhcPeriodQuery)->where('status', 'pending_approval')->count();
        $vhcRejected = (clone $vhcPeriodQuery)->where('status', 'rejected')->count();

        $vhcApprovedOrCompleted = $vhcApproved + $vhcCompleted;
        $decidedCount = $vhcApprovedOrCompleted + $vhcRejected;
        $vhcApprovalRate = $decidedCount > 0 ? (int) round(($vhcApprovedOrCompleted / $decidedCount) * 100) : 0;

        $vhcRecentRequests = VehicleRequest::with(['user', 'vehicle', 'driver'])
            ->whereBetween('start_datetime', [$vhcStartStr, $vhcEndStr])
            ->orderBy('start_datetime', 'desc')
            ->take(5)
            ->get();

        $vehicleOverview = [
            'period' => $vhcPeriodInfo['period'],
            'date_input' => $vhcPeriodInfo['date_input'],
            'start_date' => $vhcStart,
            'end_date' => $vhcEnd,
            'total_requests' => $vhcTotalRequests,
            'approved_or_completed' => $vhcApprovedOrCompleted,
            'approved_count' => $vhcApproved,
            'completed_count' => $vhcCompleted,
            'pending_count' => $vhcPending,
            'rejected_count' => $vhcRejected,
            'in_use_now' => $nowInUseCount,
            'available_units' => $availableVehiclesCount,
            'total_units' => $totalVehiclesCount,
            'approval_rate' => $vhcApprovalRate,
            'recent_requests' => $vhcRecentRequests,
        ];

        return view('admin.global_dashboard', compact(
            'meetingRoomOverview',
            'toiletInspectionOverview',
            'atkOverview',
            'sikOverview',
            'vehicleOverview'
        ));
    }

    /**
     * Handle choosing a management module.
     */
    public function selectModule(Request $request)
    {
        $request->validate([
            'module' => ['required', 'string', 'in:meeting_room,toilet_inspection,atk,sik,vehicle'],
        ]);

        $module = $request->input('module');
        session(['active_admin_module' => $module]);

        if ($module === 'meeting_room') {
            return redirect()->route('admin.dashboard');
        }

        if ($module === 'atk') {
            return redirect()->route('atk.dashboard');
        }

        if ($module === 'sik') {
            return redirect()->route('admin.sik.dashboard');
        }

        if ($module === 'vehicle') {
            return redirect()->route('vehicles.dashboard');
        }

        return redirect()->route('admin.toilet_inspection.dashboard');
    }
}
