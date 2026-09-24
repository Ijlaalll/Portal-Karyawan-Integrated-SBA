<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use App\Http\Requests\Vehicle\StoreVehicleBookingRequest;
use App\Services\VehicleRequestService;
use Illuminate\Http\Request;
use Exception;

class VehicleRequestController extends Controller
{
    public function __construct(
        protected VehicleRequestService $requestService
    ) {}

    /**
     * Display a listing of vehicle requests.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isGa = $user->isGa();
        $isManager = $isAdmin || $isGa;

        $query = VehicleRequest::with(['user', 'vehicle', 'driver', 'approver']);

        // Strict privacy rule: Regular employees can only view their own requests
        if (!$isManager) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('vehicles.requests.index', compact('requests', 'isManager', 'isAdmin', 'isGa'));
    }

    /**
     * Show the form for creating a new request.
     */
    public function create()
    {
        if (auth()->user()->isAdmin() || auth()->user()->isGa()) {
            abort(403, 'Akses tidak sah. Pengajuan kendaraan dinas hanya dilakukan oleh User/Karyawan pemohon.');
        }

        $vehicles = Vehicle::where('status', 'available')->orderBy('name', 'asc')->get();
        return view('vehicles.requests.create', compact('vehicles'));
    }

    /**
     * Store a newly created request.
     */
    public function store(StoreVehicleBookingRequest $request)
    {
        if (auth()->user()->isAdmin() || auth()->user()->isGa()) {
            abort(403, 'Akses tidak sah. Pengajuan kendaraan dinas hanya dilakukan oleh User/Karyawan pemohon.');
        }

        try {
            $vehicleRequest = $this->requestService->createRequest(auth()->user(), $request->validated());

            return redirect()->route('vehicles.requests.index')
                ->with('success', "Permohonan kendaraan #{$vehicleRequest->request_number} berhasil diajukan.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified request.
     */
    public function show(VehicleRequest $vehicleRequest)
    {
        $user = auth()->user();
        $isGa = $user->isGa();
        $isAdmin = $user->isAdmin();
        $isManager = $isAdmin || $isGa;

        // Strict privacy rule: ordinary users cannot see requests of others
        if (!$isManager && $vehicleRequest->user_id !== $user->id) {
            abort(403, 'Akses tidak sah. Anda hanya dapat melihat permohonan milik Anda sendiri.');
        }

        $vehicleRequest->load(['user', 'vehicle', 'driver', 'approver']);

        $availableVehicles = [];
        $availableDrivers = [];
        if ($isGa && $vehicleRequest->status === 'pending_approval') {
            $start = $vehicleRequest->start_datetime->format('Y-m-d H:i:s');
            $end = $vehicleRequest->end_datetime->format('Y-m-d H:i:s');

            $availableVehicles = Vehicle::where('status', 'available')
                ->whereDoesntHave('requests', function ($q) use ($start, $end, $vehicleRequest) {
                    $q->where('status', 'approved')
                      ->where('id', '!=', $vehicleRequest->id)
                      ->where('start_datetime', '<', $end)
                      ->where('end_datetime', '>', $start);
                })->orderBy('name', 'asc')->get();

            $availableDrivers = Driver::where('status', 'available')
                ->whereDoesntHave('requests', function ($q) use ($start, $end, $vehicleRequest) {
                    $q->where('status', 'approved')
                      ->where('id', '!=', $vehicleRequest->id)
                      ->where('start_datetime', '<', $end)
                      ->where('end_datetime', '>', $start);
                })->orderBy('name', 'asc')->get();
        }

        return view('vehicles.requests.show', compact('vehicleRequest', 'isGa', 'isAdmin', 'isManager', 'availableVehicles', 'availableDrivers'));
    }

    /**
     * Cancel the request (by owner when pending).
     */
    public function cancel(VehicleRequest $vehicleRequest)
    {
        try {
            $this->requestService->cancelRequest($vehicleRequest, auth()->user());

            return redirect()->route('vehicles.requests.index')
                ->with('success', 'Permohonan kendaraan berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Operational approval by GA.
     */
    public function approve(Request $request, VehicleRequest $vehicleRequest)
    {
        $this->authorizeGa();

        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id'  => ['nullable', 'exists:drivers,id'],
        ], [
            'vehicle_id.required' => 'Unit mobil operasional wajib dipilih.',
            'driver_id.exists'    => 'Supir yang dipilih tidak valid.',
        ]);

        try {
            $driverId = $request->filled('driver_id') ? (int) $request->driver_id : null;

            $this->requestService->approveRequest(
                $vehicleRequest,
                auth()->user(),
                (int) $request->vehicle_id,
                $driverId
            );

            return redirect()->route('vehicles.requests.show', $vehicleRequest->id)
                ->with('success', "Permohonan #{$vehicleRequest->request_number} berhasil disetujui dan armada telah dialokasikan.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Operational rejection by GA.
     */
    public function reject(Request $request, VehicleRequest $vehicleRequest)
    {
        $this->authorizeGa();

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan permohonan wajib diisi.',
        ]);

        try {
            $this->requestService->rejectRequest(
                $vehicleRequest,
                auth()->user(),
                $request->rejection_reason
            );

            return redirect()->route('vehicles.requests.show', $vehicleRequest->id)
                ->with('success', "Permohonan #{$vehicleRequest->request_number} telah ditolak.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function authorizeGa(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isGa()) {
            abort(403, 'Akses ditolak. Persetujuan operasional kendaraan hanya dapat dilakukan oleh GA.');
        }
    }
}
