<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkRequestItem extends Model
{
    use HasFactory;

    protected $table = 'atk_request_items';

    protected $fillable = [
        'request_id',
        'variant_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relationship: RequestItem belongs to an AtkRequest.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(AtkRequest::class, 'request_id');
    }

    /**
     * Relationship: RequestItem belongs to an AtkVariant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(AtkVariant::class, 'variant_id');
    }
}
