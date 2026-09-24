<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleFuelService
{
    /**
     * Get paginated fuel logs with zero N+1 queries.
     * Uses aliased correlated scalar subquery to deterministically select previous_odometer.
     */
    public function getFuelLogs(Request $request, int $perPage = 10)
    {
        $query = VehicleFuelLog::query()
            ->select('vehicle_fuel_logs.*')
            ->with(['vehicle', 'recorder'])
            ->addSelect([
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
            ]);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('vehicle', function ($vq) use ($search) {
                    $vq->where('name', 'like', "%{$search}%")
                       ->orWhere('plate_number', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        return $query->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get paginated fuel history for a specific vehicle.
     */
    public function getVehicleHistory(Vehicle $vehicle, int $perPage = 15)
    {
        return VehicleFuelLog::query()
            ->select('vehicle_fuel_logs.*')
            ->where('vehicle_id', $vehicle->id)
            ->with(['recorder'])
            ->addSelect([
                'previous_odometer' => VehicleFuelLog::from('vehicle_fuel_logs as prev_log')
                    ->select('prev_log.odometer')
                    ->where('prev_log.vehicle_id', $vehicle->id)
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
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Validate odometer progression strictly for the given vehicle.
     * Order: recorded_at ASC, id ASC.
     */
    public function validateOdometer(int $vehicleId, int $odometer, string $recordedAt, ?int $ignoreId = null): void
    {
        // 1. Find previous log (latest log strictly before this entry in deterministic order)
        $previousLog = VehicleFuelLog::where('vehicle_id', $vehicleId)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($recordedAt, $ignoreId) {
                $q->where('recorded_at', '<', $recordedAt);
                if ($ignoreId) {
                    $q->orWhere(function ($sub) use ($recordedAt, $ignoreId) {
                        $sub->where('recorded_at', $recordedAt)
                            ->where('id', '<', $ignoreId);
                    });
                }
            })
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();

        if ($previousLog && $odometer < $previousLog->odometer) {
            $formattedOdo = number_format($odometer, 0, ',', '.');
            $formattedPrev = number_format($previousLog->odometer, 0, ',', '.');
            throw ValidationException::withMessages([
                'odometer' => "Odometer baru ({$formattedOdo} km) tidak boleh lebih kecil dari odometer pengisian sebelumnya ({$formattedPrev} km) untuk kendaraan ini."
            ]);
        }

        // 2. If editing/updating, find next log (earliest log strictly after this entry in deterministic order)
        if ($ignoreId) {
            $nextLog = VehicleFuelLog::where('vehicle_id', $vehicleId)
                ->where('id', '!=', $ignoreId)
                ->where(function ($q) use ($recordedAt, $ignoreId) {
                    $q->where('recorded_at', '>', $recordedAt)
                      ->orWhere(function ($sub) use ($recordedAt, $ignoreId) {
                          $sub->where('recorded_at', $recordedAt)
                              ->where('id', '>', $ignoreId);
                      });
                })
                ->orderBy('recorded_at', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($nextLog && $odometer > $nextLog->odometer) {
                $formattedOdo = number_format($odometer, 0, ',', '.');
                $formattedNext = number_format($nextLog->odometer, 0, ',', '.');
                throw ValidationException::withMessages([
                    'odometer' => "Odometer ({$formattedOdo} km) tidak boleh lebih besar dari odometer pengisian berikutnya ({$formattedNext} km) untuk kendaraan ini."
                ]);
            }
        }
    }

    /**
     * Create a new fuel log.
     */
    public function createFuelLog(array $data, int $userId): VehicleFuelLog
    {
        return DB::transaction(function () use ($data, $userId) {
            $this->validateOdometer(
                (int)$data['vehicle_id'],
                (int)$data['odometer'],
                $data['recorded_at']
            );

            return VehicleFuelLog::create([
                'vehicle_id'  => $data['vehicle_id'],
                'recorded_at' => $data['recorded_at'],
                'odometer'    => (int)$data['odometer'],
                'fuel_liters' => $data['fuel_liters'],
                'fuel_cost'   => $data['fuel_cost'] ?? null,
                'notes'       => $data['notes'] ?? null,
                'recorded_by' => $userId,
            ]);
        });
    }

    /**
     * Update an existing fuel log.
     */
    public function updateFuelLog(VehicleFuelLog $log, array $data): VehicleFuelLog
    {
        return DB::transaction(function () use ($log, $data) {
            $this->validateOdometer(
                (int)$data['vehicle_id'],
                (int)$data['odometer'],
                $data['recorded_at'],
                $log->id
            );

            $log->update([
                'vehicle_id'  => $data['vehicle_id'],
                'recorded_at' => $data['recorded_at'],
                'odometer'    => (int)$data['odometer'],
                'fuel_liters' => $data['fuel_liters'],
                'fuel_cost'   => $data['fuel_cost'] ?? null,
                'notes'       => $data['notes'] ?? null,
            ]);

            return $log;
        });
    }

    /**
     * Delete a fuel log.
     */
    public function deleteFuelLog(VehicleFuelLog $log): bool
    {
        return DB::transaction(function () use ($log) {
            return $log->delete();
        });
    }

    /**
     * Calculate dynamic consumption (km/L).
     */
    public function calculateConsumption(VehicleFuelLog $log, ?int $previousOdometer = null): ?float
    {
        $prev = $previousOdometer ?? $log->getPreviousOdometer();
        if ($prev === null) {
            return null;
        }

        $distance = (int)$log->odometer - (int)$prev;
        $liters = (float)$log->fuel_liters;

        if ($distance <= 0 || $liters <= 0) {
            return null;
        }

        return round($distance / $liters, 2);
    }
}
