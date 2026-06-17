<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

abstract class TenantAwareModel extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        // Global scope for tenant isolation
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = Filament::getTenant();
            if ($tenant) {
                $tenantId = $tenant->getKey();
                $builder->where('tenant_id', $tenantId);
            }
        });

        // Auto-set tenant_id on creation
        static::creating(function ($model) {
            $tenant = Filament::getTenant();
            if ($tenant && !$model->tenant_id) {
                $model->tenant_id = $tenant->getKey();
            }
        });

        // Prevent changing tenant_id
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                throw new \Exception('Cannot change tenant_id after creation');
            }
        });
    }

    /**
     * Scope to bypass tenant isolation (use with caution!)
     */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope for manual tenant filtering
     */
    public function scopeForTenant(Builder $query, $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get the current tenant ID from Filament
     */
    protected function getCurrentTenantId()
    {
        $tenant = Filament::getTenant();
        return $tenant ? $tenant->getKey() : null;
    }
}