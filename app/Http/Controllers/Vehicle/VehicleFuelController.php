<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use App\Http\Requests\Vehicle\StoreVehicleFuelLogRequest;
use App\Http\Requests\Vehicle\UpdateVehicleFuelLogRequest;
use App\Services\VehicleFuelService;
use Illuminate\Http\Request;

class VehicleFuelController extends Controller
{
    public function __construct(
        protected VehicleFuelService $fuelService
    ) {}

    /**
     * Display listing of fuel logs.
     */
    public function index(Request $request)
    {
        $this->authorizeAdminOrGa();

        $fuelLogs = $this->fuelService->getFuelLogs($request);
        $vehicles = Vehicle::orderBy('name', 'asc')->get();

        return view('vehicles.fuel.index', compact('fuelLogs', 'vehicles'));
    }

    /**
     * Show form for creating a new fuel log.
     */
    public function create(Request $request)
    {
        $this->authorizeGaOnly();

        $vehicles = Vehicle::where('status', '!=', 'inactive')
            ->orderBy('name', 'asc')
            ->get();
        $selectedVehicleId = $request->query('vehicle_id');

        return view('vehicles.fuel.create', compact('vehicles', 'selectedVehicleId'));
    }

    /**
     * Store newly created fuel log.
     */
    public function store(StoreVehicleFuelLogRequest $request)
    {
        $this->authorizeGaOnly();

        $this->fuelService->createFuelLog($request->validated(), auth()->id());

        return redirect()->route('vehicles.fuel.index')
            ->with('success', 'Catatan pengisian BBM berhasil ditambahkan.');
    }

    /**
     * Show fuel history for a specific vehicle.
     */
    public function history(Vehicle $vehicle)
    {
        $this->authorizeAdminOrGa();

        $fuelLogs = $this->fuelService->getVehicleHistory($vehicle);

        return view('vehicles.fuel.history', compact('vehicle', 'fuelLogs'));
    }

    /**
     * Show form for editing a fuel log.
     */
    public function edit(VehicleFuelLog $fuelLog)
    {
        $this->authorizeGaOnly();

        $vehicles = Vehicle::orderBy('name', 'asc')->get();

        return view('vehicles.fuel.edit', compact('fuelLog', 'vehicles'));
    }

    /**
     * Update fuel log.
     */
    public function update(UpdateVehicleFuelLogRequest $request, VehicleFuelLog $fuelLog)
    {
        $this->authorizeGaOnly();

        $this->fuelService->updateFuelLog($fuelLog, $request->validated());

        return redirect()->route('vehicles.fuel.index')
            ->with('success', 'Data pengisian BBM berhasil diperbarui.');
    }

    /**
     * Delete fuel log.
     */
    public function destroy(VehicleFuelLog $fuelLog)
    {
        $this->authorizeGaOnly();

        $this->fuelService->deleteFuelLog($fuelLog);

        return redirect()->route('vehicles.fuel.index')
            ->with('success', 'Catatan pengisian BBM berhasil dihapus.');
    }

    private function authorizeAdminOrGa(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isGa())) {
            abort(403, 'Akses tidak sah. Diperlukan hak akses Admin atau GA.');
        }
    }

    private function authorizeGaOnly(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isGa()) {
            abort(403, 'Akses tidak sah. Pengelolaan data pengisian BBM hanya dapat dilakukan oleh GA.');
        }
    }
}
