<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'id',
        'tenant_id',
        'order_id',
        'transaction_type',
        'payment_method',
        'amount',
        'currency_code',
        'exchange_rate',
        'cash_tendered',
        'cash_change',
        'cash_denominations',
        'card_reference',
        'card_last_four',
        'card_authorization_code',
        'mpesa_code',
        'mpesa_phone',
        'mpesa_result_code',
        'mpesa_result_description',
        'bank_reference',
        'bank_account_last_four',
        'status',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'cash_tendered' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'cash_denominations' => 'array',
        'processed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}