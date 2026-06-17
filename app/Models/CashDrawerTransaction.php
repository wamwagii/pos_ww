<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDrawerTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_drawer_transactions';

    protected $fillable = [
        'id',
        'tenant_id',
        'drawer_id',
        'order_id',
        'user_id',
        'transaction_type',
        'amount',
        'currency_code',
        'reason',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function drawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}