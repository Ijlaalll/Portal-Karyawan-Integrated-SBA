<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Driver;

class VehicleService
{
    public function createVehicle(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);
        return $vehicle;
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }

    public function createDriver(array $data): Driver
    {
        return Driver::create($data);
    }

    public function updateDriver(Driver $driver, array $data): Driver
    {
        $driver->update($data);
        return $driver;
    }

    public function deleteDriver(Driver $driver): bool
    {
        return $driver->delete();
    }
}
