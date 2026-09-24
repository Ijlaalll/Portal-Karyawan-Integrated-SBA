<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostCenterTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_id',
        'user_id',
        'activity_type',
        'reference_id',
        'reference_item_id',
        'description',
        'item_name',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
    ];

    protected $attributes = [
        'reference_item_id' => 0,
        'status' => 'posted',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Relationship: Transaction belongs to a Cost Center.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    /**
     * Relationship: Transaction belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Booking for room booking activity.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'reference_id');
    }

    /**
     * Relationship: ATK Request for ATK usage activity.
     */
    public function atkRequest(): BelongsTo
    {
        return $this->belongsTo(AtkRequest::class, 'reference_id');
    }

    /**
     * Relationship: ATK Request Item for ATK usage activity item.
     */
    public function atkRequestItem(): BelongsTo
    {
        return $this->belongsTo(AtkRequestItem::class, 'reference_item_id');
    }

    /**
     * Check if transaction is an original posted transaction.
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if transaction is a reversal.
     */
    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }
}
