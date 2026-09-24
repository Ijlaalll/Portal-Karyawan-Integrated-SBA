<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'recorded_at',
        'odometer',
        'fuel_liters',
        'fuel_cost',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'odometer' => 'integer',
        'fuel_liters' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the previous odometer deterministically (recorded_at ASC, id ASC).
     * If already selected via subquery in query builder, returns that value to avoid N+1.
     */
    public function getPreviousOdometer(): ?int
    {
        if (array_key_exists('previous_odometer', $this->attributes)) {
            return $this->attributes['previous_odometer'] !== null ? (int)$this->attributes['previous_odometer'] : null;
        }

        return static::where('vehicle_id', $this->vehicle_id)
            ->where(function ($q) {
                $q->where('recorded_at', '<', $this->recorded_at)
                  ->orWhere(function ($sub) {
                      $sub->where('recorded_at', $this->recorded_at)
                          ->where('id', '<', $this->id);
                  });
            })
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->value('odometer');
    }

    /**
     * Fully derived distance calculated from previous odometer.
     */
    public function getDistanceAttribute(): ?int
    {
        $prevOdo = $this->getPreviousOdometer();
        if ($prevOdo === null) {
            return null;
        }

        return max(0, (int)$this->odometer - (int)$prevOdo);
    }

    /**
     * Fully derived dynamic consumption (km/L).
     * Formula: (current_odometer - previous_odometer) / current_fuel_liters
     * Returns null if first fill-up, distance <= 0, or liters <= 0.
     */
    public function getConsumptionAttribute(): ?float
    {
        $prevOdo = $this->getPreviousOdometer();
        if ($prevOdo === null) {
            return null;
        }

        $distance = (int)$this->odometer - (int)$prevOdo;
        $liters = (float)$this->fuel_liters;

        if ($distance <= 0 || $liters <= 0) {
            return null;
        }

        return round($distance / $liters, 2);
    }
}
