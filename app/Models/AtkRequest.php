<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkRequest extends Model
{
    use HasFactory;

    protected $table = 'atk_requests';

    protected $fillable = [
        'request_number',
        'user_id',
        'status',
        'note',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'completed_by',
        'completed_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationship: Request belongs to a User (the requester).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Request has many items requested.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AtkRequestItem::class, 'request_id');
    }

    /**
     * Relationship: Request has many status histories.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(AtkRequestHistory::class, 'request_id');
    }

    /**
     * Relationship: Request is approved by a User (GA/Admin).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Request is processed by a User.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Relationship: Request is completed by a User.
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Relationship: Request is rejected by a User.
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Relationship: Request is cancelled by a User.
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
