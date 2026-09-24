<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ExitPermit extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_OUTSIDE = 'outside';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CLOSED_MANUALLY = 'closed_manually';

    protected $fillable = [
        'sik_number',
        'user_id',
        'department',
        'position',
        'employee_id',
        'purpose_type',
        'purpose',
        'purpose_detail',
        'permit_date',
        'expected_departure_time',
        'expected_return_time',
        'vehicle_type',
        'vehicle_details',
        'status',
        'approver_id',
        'is_replacement_approver',
        'approved_at',
        'approval_note',
        'rejection_reason',
        'qr_token',
        'actual_departure_at',
        'actual_return_at',
        'exit_scanned_by',
        'return_scanned_by',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
        'closed_by',
        'closed_at',
        'close_reason',
    ];

    protected $casts = [
        'permit_date' => 'date',
        'is_replacement_approver' => 'boolean',
        'approved_at' => 'datetime',
        'actual_departure_at' => 'datetime',
        'actual_return_at' => 'datetime',
        'revoked_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Relationship: Applicant User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Approver User.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Relationship: Security Scanned Exit.
     */
    public function exitScannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exit_scanned_by');
    }

    /**
     * Relationship: Security Scanned Return.
     */
    public function returnScannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_scanned_by');
    }

    /**
     * Relationship: User who Revoked.
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Relationship: User who Closed Manually.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Relationship: Approval & Decision Histories.
     */
    public function approvalHistories(): HasMany
    {
        return $this->hasMany(SikApprovalHistory::class, 'exit_permit_id')->orderBy('actioned_at', 'asc');
    }

    /**
     * Relationship: Gate Scan Logs.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(SikScanLog::class, 'exit_permit_id')->orderBy('scanned_at', 'asc');
    }

    /**
     * Expected departure Carbon instance in Asia/Jakarta.
     */
    public function getExpectedDepartureDatetimeAttribute(): ?Carbon
    {
        if (!$this->permit_date || !$this->expected_departure_time) {
            return null;
        }
        try {
            $time = substr($this->expected_departure_time, 0, 5);
            return Carbon::createFromFormat('Y-m-d H:i', $this->permit_date->format('Y-m-d') . ' ' . $time, 'Asia/Jakarta');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Expected return Carbon instance in Asia/Jakarta.
     */
    public function getExpectedReturnDatetimeAttribute(): ?Carbon
    {
        if (!$this->permit_date || !$this->expected_return_time) {
            return null;
        }
        try {
            $time = substr($this->expected_return_time, 0, 5);
            return Carbon::createFromFormat('Y-m-d H:i', $this->permit_date->format('Y-m-d') . ' ' . $time, 'Asia/Jakarta');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Dynamic effective status calculation.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === self::STATUS_OUTSIDE) {
            $returnDatetime = $this->expected_return_datetime;
            if ($returnDatetime && Carbon::now('Asia/Jakarta')->greaterThan($returnDatetime)) {
                return self::STATUS_OVERDUE;
            }
        }
        return $this->status;
    }

    /**
     * Get actual duration in seconds.
     */
    public function getActualDurationSecondsAttribute(): ?int
    {
        if (!$this->actual_departure_at) {
            return null;
        }

        $end = $this->actual_return_at ?? Carbon::now('Asia/Jakarta');
        return max(0, $this->actual_departure_at->diffInSeconds($end));
    }

    /**
     * Human-readable formatted duration (e.g., '1 jam 15 menit', '48 menit', '12 detik').
     */
    public function getActualDurationFormattedAttribute(): string
    {
        $seconds = $this->actual_duration_seconds;
        if ($seconds === null) {
            return '-';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
        }
        if ($minutes > 0) {
            return "{$minutes} menit";
        }
        return "{$secs} detik";
    }

    /**
     * Get late duration in seconds past expected return time.
     */
    public function getLateDurationSecondsAttribute(): int
    {
        $expected = $this->expected_return_datetime;
        if (!$expected) {
            return 0;
        }

        $compareTime = null;
        if ($this->actual_return_at) {
            $compareTime = $this->actual_return_at;
        } elseif ($this->status === self::STATUS_OUTSIDE || $this->effective_status === self::STATUS_OVERDUE) {
            $compareTime = Carbon::now('Asia/Jakarta');
        }

        if ($compareTime && $compareTime->greaterThan($expected)) {
            return $expected->diffInSeconds($compareTime);
        }

        return 0;
    }

    /**
     * Human-readable formatted late duration.
     */
    public function getLateDurationFormattedAttribute(): string
    {
        $seconds = $this->late_duration_seconds;
        if ($seconds <= 0) {
            return '-';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
        }
        return "{$minutes} menit";
    }

    /**
     * Check if permit is late / was returned late.
     */
    public function getIsLateAttribute(): bool
    {
        return $this->late_duration_seconds > 0;
    }

    /**
     * Status Checks.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isOutside(): bool
    {
        return $this->status === self::STATUS_OUTSIDE;
    }

    public function isOverdue(): bool
    {
        return $this->effective_status === self::STATUS_OVERDUE;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isClosedManually(): bool
    {
        return $this->status === self::STATUS_CLOSED_MANUALLY;
    }

    /**
     * Check if permit can be cancelled by applicant.
     */
    public function canBeCancelledBy(User $user): bool
    {
        return $this->user_id === $user->id && in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL], true);
    }

    /**
     * Check if permit can be revoked (only when APPROVED and before departure).
     */
    public function canBeRevoked(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->actual_departure_at === null;
    }

    /**
     * Check if permit can be closed manually (when OUTSIDE or OVERDUE, applicant did not return).
     */
    public function canBeClosedManually(): bool
    {
        return in_array($this->status, [self::STATUS_OUTSIDE, self::STATUS_OVERDUE], true) && $this->actual_return_at === null;
    }

    /**
     * Check if QR is valid for EXIT scan.
     */
    public function isQrValidForExit(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->actual_departure_at === null && !empty($this->qr_token);
    }

    /**
     * Check if QR is valid for RETURN scan.
     */
    public function isQrValidForReturn(): bool
    {
        return ($this->status === self::STATUS_OUTSIDE || $this->effective_status === self::STATUS_OVERDUE) 
            && $this->actual_return_at === null 
            && !empty($this->qr_token);
    }

    /**
     * Scopes.
     */
    public function scopeActiveOutside(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OUTSIDE);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $currentTime = $now->format('H:i');

        return $query->where('status', self::STATUS_OUTSIDE)
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('permit_date', '<', $today)
                  ->orWhere(function ($sub) use ($today, $currentTime) {
                      $sub->where('permit_date', '=', $today)
                          ->where('expected_return_time', '<', $currentTime);
                  });
            });
    }
}
