<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'status',
        'last_success_at',
        'last_failure_at',
        'last_error_message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the histories for this SMTP setting.
     */
    public function histories()
    {
        return $this->hasMany(SmtpHistory::class, 'host', 'host');
    }
}
