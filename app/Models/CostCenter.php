<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_code',
        'name',
        'description',
        'person_responsible',
        'company_code',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Helper to check if cost center is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Relationship: Cost Center has many Users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'cost_center_id');
    }

    /**
     * Relationship: Cost Center has many Transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CostCenterTransaction::class, 'cost_center_id');
    }

    /**
     * Calculate total expenditure for this cost center.
     */
    public function getTotalExpenditureAttribute(): float
    {
        return (float) $this->transactions()->sum('total_amount');
    }
}
