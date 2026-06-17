<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_sessions';

    protected $fillable = [
        'id',
        'user_id',
        'tenant_id',
        'session_token',
        'device_info',
        'ip_address',
        'started_at',
        'last_activity',
        'ended_at',
        'is_active',
    ];

    protected $casts = [
        'device_info' => 'array',
        'started_at' => 'datetime',
        'last_activity' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->ended_at === null;
    }
}