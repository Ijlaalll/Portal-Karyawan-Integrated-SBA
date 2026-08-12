<?php

namespace App\Http\Controllers\ToiletInspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\ToiletLocation;
use App\Models\ChecklistItem;
use App\Services\InspectionService;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function __construct(
        protected InspectionService $inspectionService
    ) {}

    /**
     * Display a listing of inspections.
     */
    /**
     * Display a listing of inspections.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Inspection::with(['location', 'inspector', 'approval.approver'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin() && !$user->isSpv()) {
            // Regular inspector can only see their own inspections
            $query->where('inspector_id', $user->id);
        }

        // Search: inspector name, building, area/office
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('inspector', function ($qi) use ($search) {
                    $qi->where('name', 'like', "%{$search}%");
                })->orWhereHas('location', function ($ql) use ($search) {
                    $ql->where('building', 'like', "%{$search}%")
                       ->orWhere('area', 'like', "%{$search}%");
                });
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Building
        if ($request->filled('building')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('building', $request->input('building'));
            });
        }

        // Filter: Floor
        if ($request->filled('floor')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('floor', $request->input('floor'));
            });
        }

        // Filter: Gender
        if ($request->filled('gender')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('gender', $request->input('gender'));
            });
        }

        // Filter: Inspector (only for SPV/Admin)
        if (($user->isAdmin() || $user->isSpv()) && $request->filled('inspector_id')) {
            $query->where('inspector_id', $request->input('inspector_id'));
        }

        // Filter: Date
        if ($request->filled('date_filter')) {
            $dateFilter = $request->input('date_filter');
            
            if ($dateFilter === 'today') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'this_week') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfWeek()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfWeek()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'this_month') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfMonth()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfMonth()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
        }

        $inspections = $query->paginate(10)->withQueryString();

        // Get filter options datasets dynamically
        $buildings = ToiletLocation::where('is_active', true)->distinct()->orderBy('building')->pluck('building');
        $floors = ToiletLocation::where('is_active', true)->distinct()->orderBy('floor')->pluck('floor');
        
        $inspectors = collect();
        if ($user->isAdmin() || $user->isSpv()) {
            $inspectors = \App\Models\User::whereHas('modules', fn($q) => $q->where('module', 'toilet_inspection'))
                ->where('role', 'user')
                ->orderBy('name')
                ->get();
        }

        return view('toilet_inspection.inspections.index', compact(
            'inspections',
            'buildings',
            'floors',
            'inspectors'
        ));
    }

    /**
     * Show the form for creating a new inspection.
     */
    public function create()
    {
        if (!auth()->user()->isUser()) {
            abort(403, 'Only inspectors can perform toilet inspections.');
        }

        $locations = ToiletLocation::where('is_active', true)
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('toilet_name')
            ->get();

        $checklistItems = ChecklistItem::where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($checklistItems->isEmpty()) {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'No active checklist items configured. Please contact administrator.');
        }

        return view('toilet_inspection.inspections.create', compact('locations', 'checklistItems'));
    }

    /**
     * Store a newly created inspection in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isUser()) {
            abort(403, 'Only inspectors can perform toilet inspections.');
        }

        $request->validate([
            'location_id' => ['required', 'exists:toilet_locations,id'],
            'notes' => ['nullable', 'string'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'results' => ['required', 'array'],
            'results.*.is_ok' => ['required', 'boolean'],
            'results.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $activeChecklistItems = ChecklistItem::where('is_active', true)->pluck('id')->toArray();
        $resultsKeys = array_keys($request->input('results', []));

        foreach ($activeChecklistItems as $itemId) {
            if (!in_array($itemId, $resultsKeys) || !isset($request->input('results')[$itemId]['is_ok'])) {
                return back()->withInput()->withErrors(['results' => 'All active checklist items must be completed.']);
            }
        }

        $data = $request->only(['location_id', 'notes', 'results']);
        $data['status'] = 'submitted'; // Auto-submit to SPV

        $this->inspectionService->createInspection($data, $request->file('photo'));

        return redirect()->route('toilet_inspection.dashboard')
            ->with('success', 'Inspection submitted successfully for review.');
    }

    /**
     * Display the Toilet Inspection report page.
     */
    public function report(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSpv()) {
            abort(403, 'Only administrators and supervisors can access reporting.');
        }

        $query = Inspection::with(['location', 'inspector', 'approval.approver'])
            ->orderBy('created_at', 'desc');

        // Search: inspector name, building, area/office
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('inspector', function ($qi) use ($search) {
                    $qi->where('name', 'like', "%{$search}%");
                })->orWhereHas('location', function ($ql) use ($search) {
                    $ql->where('building', 'like', "%{$search}%")
                       ->orWhere('area', 'like', "%{$search}%");
                });
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Building
        if ($request->filled('building')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('building', $request->input('building'));
            });
        }

        // Filter: Floor
        if ($request->filled('floor')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('floor', $request->input('floor'));
            });
        }

        // Filter: Gender
        if ($request->filled('gender')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('gender', $request->input('gender'));
            });
        }

        // Filter: Inspector
        if ($request->filled('inspector_id')) {
            $query->where('inspector_id', $request->input('inspector_id'));
        }

        // Filter: Date
        if ($request->filled('date_filter')) {
            $dateFilter = $request->input('date_filter');
            
            if ($dateFilter === 'today') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'this_week') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfWeek()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfWeek()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'this_month') {
                $start = \Carbon\Carbon::now('Asia/Jakarta')->startOfMonth()->setTimezone('UTC');
                $end = \Carbon\Carbon::now('Asia/Jakarta')->endOfMonth()->setTimezone('UTC');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFilter === 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay()->setTimezone('UTC');
                    $end = \Carbon\Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay()->setTimezone('UTC');
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
        }

        // Summary Statistics (calculated from the filtered dataset)
        $totalInspections = (clone $query)->count();
        $pendingCount = (clone $query)->where('status', 'submitted')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();
        $rejectedCount = (clone $query)->where('status', 'rejected')->count();

        // Paginate results
        $inspections = $query->paginate(15)->withQueryString();

        // Get filter options datasets dynamically
        $buildings = ToiletLocation::where('is_active', true)->distinct()->orderBy('building')->pluck('building');
        $floors = ToiletLocation::where('is_active', true)->distinct()->orderBy('floor')->pluck('floor');
        $inspectors = \App\Models\User::whereHas('modules', fn($q) => $q->where('module', 'toilet_inspection'))
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('toilet_inspection.reports.index', compact(
            'inspections',
            'buildings',
            'floors',
            'inspectors',
            'totalInspections',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Display the specified inspection.
     */
    public function show(Inspection $inspection)
    {
        // Check if user is authorized to view this inspection
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSpv() && $inspection->inspector_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $inspection->load(['location', 'inspector', 'results.checklistItem', 'approval.approver']);

        return view('toilet_inspection.inspections.show', compact('inspection'));
    }
}
