<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SikScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'exit_permit_id',
        'scanned_by',
        'scan_type',
        'scanned_at',
        'gate',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Relationship: Exit Permit.
     */
    public function exitPermit(): BelongsTo
    {
        return $this->belongsTo(ExitPermit::class, 'exit_permit_id');
    }

    /**
     * Relationship: Security Scanned User.
     */
    public function security(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
