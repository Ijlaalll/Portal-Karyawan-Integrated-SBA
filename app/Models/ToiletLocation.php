<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToiletLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'building',
        'floor',
        'area',
        'toilet_name',
        'gender',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get inspections for this location.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'location_id');
    }
}
