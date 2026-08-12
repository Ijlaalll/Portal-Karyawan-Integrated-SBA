<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'inspector_id',
        'status',
        'notes',
        'photo_path',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    /**
     * Get the location being inspected.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(ToiletLocation::class, 'location_id');
    }

    /**
     * Get the inspector who performed this inspection.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Get checklist item results for this inspection.
     */
    public function results(): HasMany
    {
        return $this->hasMany(InspectionResult::class, 'inspection_id');
    }

    /**
     * Get the approval/rejection details.
     */
    public function approval(): HasOne
    {
        return $this->hasOne(InspectionApproval::class, 'inspection_id');
    }
}
