<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkRequestHistory extends Model
{
    use HasFactory;

    protected $table = 'atk_request_histories';

    protected $fillable = [
        'request_id',
        'status',
        'notes',
        'actor_id',
    ];

    /**
     * Relationship: RequestHistory belongs to an AtkRequest.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(AtkRequest::class, 'request_id');
    }

    /**
     * Relationship: RequestHistory belongs to a User (the actor).
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
