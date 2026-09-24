<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DepartmentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'hod_user_id',
        'hod_approver_user_id',
        'notes',
    ];

    /**
     * Relationship: HOD User.
     */
    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    /**
     * Relationship: Approver / Atasan HOD User.
     */
    public function hodApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_approver_user_id');
    }

    /**
     * Relationship: Authorized Replacement Approvers.
     */
    public function replacementApprovers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_replacement_approvers');
    }
}

