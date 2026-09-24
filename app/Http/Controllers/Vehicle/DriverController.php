<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use App\Http\Requests\Vehicle\StoreDriverRequest;
use App\Http\Requests\Vehicle\UpdateDriverRequest;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    /**
     * Display listing of drivers.
     */
    public function index(Request $request)
    {
        $query = Driver::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
        }

        $drivers = $query->orderBy('name', 'asc')->paginate(10);

        return view('vehicles.drivers.index', compact('drivers'));
    }

    /**
     * Show form for creating driver.
     */
    public function create()
    {
        $this->authorizeAdminOrGa();
        $users = User::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('vehicles.drivers.create', compact('users'));
    }

    /**
     * Store newly created driver.
     */
    public function store(StoreDriverRequest $request)
    {
        $this->vehicleService->createDriver($request->validated());

        return redirect()->route('vehicles.drivers.index')
            ->with('success', 'Supir berhasil ditambahkan.');
    }

    /**
     * Show form for editing driver.
     */
    public function edit(Driver $driver)
    {
        $this->authorizeAdminOrGa();
        $users = User::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('vehicles.drivers.edit', compact('driver', 'users'));
    }

    /**
     * Update driver.
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $this->vehicleService->updateDriver($driver, $request->validated());

        return redirect()->route('vehicles.drivers.index')
            ->with('success', 'Data supir berhasil diperbarui.');
    }

    /**
     * Delete driver.
     */
    public function destroy(Driver $driver)
    {
        $this->authorizeAdminOrGa();

        $this->vehicleService->deleteDriver($driver);

        return redirect()->route('vehicles.drivers.index')
            ->with('success', 'Supir berhasil dihapus.');
    }

    private function authorizeAdminOrGa(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isGa())) {
            abort(403, 'Akses tidak sah. Diperlukan hak akses Admin atau GA.');
        }
    }
}
