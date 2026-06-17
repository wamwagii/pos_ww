<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $table = 'users';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'email',
        'password_hash',
        'remember_token',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'pin_code',
        'barcode',
        'fingerprint_hash',
        'biometric_device_id',
    ];

    protected $hidden = [
        'password_hash',
        'pin_code',
        'fingerprint_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'id' => 'string',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // ===== ROLE CHECK METHODS =====
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return in_array($this->role, ['supervisor', 'admin', 'manager']);
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    // ===== FILAMENT USERNAME METHODS =====
    public function getFilamentName(): string
    {
        return $this->getFullNameAttribute();
    }

    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    // ===== TENANT RELATIONSHIPS =====
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getTenants(): array|Collection
    {
        if ($this->tenant) {
            return collect([$this->tenant]);
        }
        return collect([]);
    }

    public function canAccessTenant(Tenant $tenant): bool
    {
        return $this->tenant_id === $tenant->id;
    }

    // ===== RELATIONSHIPS =====
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function voidedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'voided_by');
    }

    public function authorizedActions(): HasMany
    {
        return $this->hasMany(AuthorizationLog::class, 'supervisor_id');
    }

    public function cashierActions(): HasMany
    {
        return $this->hasMany(AuthorizationLog::class, 'cashier_id');
    }

    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}