<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

class SetTenantDatabaseContext
{
    public function handle(Request $request, Closure $next)
    {
        // Get the current tenant from Filament
        $tenant = Filament::getTenant();
        
        if ($tenant) {
            $tenantId = $tenant->getKey();
            
            // Set PostgreSQL session variable for RLS
            DB::statement("SELECT set_config('app.current_tenant', ?, true)", [$tenantId]);
            
            // Also set for the current request
            $request->merge(['tenant_id' => $tenantId]);
        }
        
        return $next($request);
    }
}