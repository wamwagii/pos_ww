<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'id',
        'tenant_id',
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'tax_amount',
        'discount_amount',
        'discount_percentage',
        'discount_reason',
        'is_removed',
        'removed_at',
        'removed_by',
        'removal_reason',
        'removal_authorization_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_removed' => 'boolean',
        'removed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function authorization(): BelongsTo
    {
        return $this->belongsTo(AuthorizationLog::class, 'removal_authorization_id');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}