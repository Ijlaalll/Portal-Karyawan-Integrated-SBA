<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AtkStockMovement extends Model
{
    use HasFactory;

    protected $table = 'atk_stock_movements';

    protected $fillable = [
        'variant_id',
        'type',
        'reference_id',
        'reference_type',
        'previous_stock',
        'quantity',
        'new_stock',
        'reason',
        'actor_id',
    ];

    protected $casts = [
        'previous_stock' => 'integer',
        'quantity' => 'integer',
        'new_stock' => 'integer',
    ];

    /**
     * Relationship: StockMovement belongs to an AtkVariant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(AtkVariant::class, 'variant_id');
    }

    /**
     * Relationship: StockMovement belongs to a User (the actor).
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Polymorphic Relationship: Get the referencing entity.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
