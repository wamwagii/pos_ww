<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizationLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'authorization_logs';

    protected $fillable = [
        'id',
        'tenant_id',
        'action_type',
        'cashier_id',
        'supervisor_id',
        'authorization_method',
        'reference_table',
        'reference_id',
        'action_details',
        'before_data',
        'after_data',
        'authorized_at',
    ];

    protected $casts = [
        'action_details' => 'array',
        'before_data' => 'array',
        'after_data' => 'array',
        'authorized_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}