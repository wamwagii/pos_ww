<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends TenantAwareModel
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'tenant_id',
        'sku',
        'name',
        'description',
        'category',
        'unit_price',
        'currency_code',
        'tax_rate',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPriceWithCurrencyAttribute(): string
    {
        return $this->currency?->symbol . ' ' . number_format($this->unit_price, 2);
    }
}