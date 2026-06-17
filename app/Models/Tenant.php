<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tenants';

    protected $fillable = [
        'id',
        'name',
        'domain',
        'country',
        'currency_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Tell Laravel to use 'id' for route binding
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'tenant_currencies')
                    ->withPivot('is_default', 'exchange_rate')
                    ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function authorizationLogs(): HasMany
    {
        return $this->hasMany(AuthorizationLog::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function cashDrawers(): HasMany
    {
        return $this->hasMany(CashDrawer::class);
    }
}