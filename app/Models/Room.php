<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'description',
        'price_per_hour',
        'is_pricing_active',
        'status',
        'image',
    ];

    protected $attributes = [
        'price_per_hour' => 0.00,
        'is_pricing_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_per_hour' => 'decimal:2',
            'is_pricing_active' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPricingActive(): bool
    {
        return (bool) $this->is_pricing_active;
    }

    /**
     * Get the public URL for the room's image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'room_id');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'room_facilities');
    }
}
