<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    /**
     * Display listing of vehicles.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->orderBy('name', 'asc')->paginate(10);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show form for creating a new vehicle.
     */
    public function create()
    {
        $this->authorizeAdminOrGa();
        return view('vehicles.create');
    }

    /**
     * Store newly created vehicle.
     */
    public function store(StoreVehicleRequest $request)
    {
        $this->vehicleService->createVehicle($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    /**
     * Show form for editing vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorizeAdminOrGa();
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update vehicle.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $this->vehicleService->updateVehicle($vehicle, $request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    /**
     * Delete vehicle.
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeAdminOrGa();

        $this->vehicleService->deleteVehicle($vehicle);

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }

    private function authorizeAdminOrGa(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isGa())) {
            abort(403, 'Akses tidak sah. Diperlukan hak akses Admin atau GA.');
        }
    }
}
