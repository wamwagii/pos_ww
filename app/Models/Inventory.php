<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inventory';

    protected $fillable = [
        'id',
        'tenant_id',
        'product_id',
        'quantity',
        'reorder_level',
        'warehouse_location',
        'last_counted_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'last_counted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}