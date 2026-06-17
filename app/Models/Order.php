<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'tenant_id',
        'order_number',
        'customer_id',
        'cashier_id',
        'order_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency_code',
        'payment_method',
        'payment_status',
        'payment_reference',
        'amount_tendered',
        'change_due',
        'cash_denominations',
        'card_last_four',
        'card_type',
        'mpesa_code',
        'mpesa_phone',
        'receipt_number',
        'receipt_printed_at',
        'notes',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change_due' => 'decimal:2',
        'cash_denominations' => 'array',
        'order_date' => 'datetime',
        'receipt_printed_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function getChangeDueFormattedAttribute(): string
    {
        return $this->currency?->symbol . ' ' . number_format($this->change_due ?? 0, 2);
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return $this->currency?->symbol . ' ' . number_format($this->total_amount, 2);
    }
}