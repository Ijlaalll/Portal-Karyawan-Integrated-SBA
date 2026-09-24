<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SikApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'exit_permit_id',
        'approver_id',
        'approver_type',
        'status',
        'note',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    /**
     * Relationship: Exit Permit.
     */
    public function exitPermit(): BelongsTo
    {
        return $this->belongsTo(ExitPermit::class, 'exit_permit_id');
    }

    /**
     * Relationship: Approver User.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
