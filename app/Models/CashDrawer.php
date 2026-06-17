<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashDrawer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_drawers';

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'location',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference',
        'status',
        'opened_at',
        'opened_by',
        'closed_at',
        'closed_by',
        'reconciled_at',
        'reconciled_by',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashDrawerTransaction::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isReconciled(): bool
    {
        return $this->status === 'reconciled';
    }
}