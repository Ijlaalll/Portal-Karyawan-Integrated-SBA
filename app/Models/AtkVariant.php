<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkVariant extends Model
{
    use HasFactory;

    protected $table = 'atk_variants';

    protected $fillable = [
        'item_id',
        'name',
        'sku',
        'unit',
        'purchase_price',
        'stock',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Variant belongs to an Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    /**
     * Relationship: Variant has many Request Items.
     */
    public function requestItems(): HasMany
    {
        return $this->hasMany(AtkRequestItem::class, 'variant_id');
    }

    /**
     * Relationship: Variant has many Stock Movements.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(AtkStockMovement::class, 'variant_id');
    }

    /**
     * Accessor: Get derived Availability Status of stock.
     * Rules:
     * - stock <= 0 => 'Habis'
     * - stock <= minimum_stock => 'Stok Menipis'
     * - stock > minimum_stock => 'Tersedia'
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Habis';
        }
        if ($this->stock <= $this->minimum_stock) {
            return 'Stok Menipis';
        }
        return 'Tersedia';
    }

    /**
     * Helper check: is variant currently available for request?
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }
}
