<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'name',
        'fuel_type',
        'status',
        'notes',
    ];

    protected $attributes = [
        'fuel_type' => 'Bensin',
        'status' => 'available',
    ];

    public function setPlateNumberAttribute($value): void
    {
        $this->attributes['plate_number'] = strtoupper(trim((string)$value));
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function requests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'vehicle_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(VehicleFuelLog::class, 'vehicle_id');
    }
}
