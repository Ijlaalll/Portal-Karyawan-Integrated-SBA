<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmtpHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'smtp_histories';

    /**
     * Disable updated_at column.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'host',
        'port',
        'encryption',
        'username',
        'from_address',
        'from_name',
        'action',
    ];

    /**
     * Get the user that changed the settings.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
