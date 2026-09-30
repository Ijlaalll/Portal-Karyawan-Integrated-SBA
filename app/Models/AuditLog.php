<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: An audit log belongs to a user (optional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for searching audit logs.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty(trim((string) $term))) {
            return $query;
        }

        $term = trim($term);
        return $query->where(function ($q) use ($term) {
            $q->where('user_name', 'like', "%{$term}%")
              ->orWhere('user_email', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('ip_address', 'like', "%{$term}%")
              ->orWhere('action', 'like', "%{$term}%")
              ->orWhere('module', 'like', "%{$term}%");
        });
    }

    /**
     * Scope for filtering by module.
     */
    public function scopeFilterModule($query, ?string $module)
    {
        if (!empty($module) && $module !== 'all') {
            return $query->where('module', $module);
        }
        return $query;
    }

    /**
     * Scope for filtering by action.
     */
    public function scopeFilterAction($query, ?string $action)
    {
        if (!empty($action) && $action !== 'all') {
            return $query->where('action', $action);
        }
        return $query;
    }

    /**
     * Scope for filtering by date range preset.
     */
    public function scopeFilterPeriod($query, ?string $period)
    {
        $now = now();
        return match ($period) {
            'today' => $query->whereDate('created_at', $now->toDateString()),
            'yesterday' => $query->whereDate('created_at', $now->subDay()->toDateString()),
            'week' => $query->where('created_at', '>=', $now->subDays(7)->startOfDay()),
            'month' => $query->where('created_at', '>=', $now->subDays(30)->startOfDay()),
            default => $query,
        };
    }
}
