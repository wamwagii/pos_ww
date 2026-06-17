<?php

namespace App\Filament\POS\Resources\Tenants;

use App\Filament\POS\Resources\Tenants\Pages\CreateTenant;
use App\Filament\POS\Resources\Tenants\Pages\EditTenant;
use App\Filament\POS\Resources\Tenants\Pages\ListTenants;
use App\Filament\POS\Resources\Tenants\Pages\ViewTenant;
use App\Filament\POS\Resources\Tenants\Schemas\TenantForm;
use App\Filament\POS\Resources\Tenants\Schemas\TenantInfolist;
use App\Filament\POS\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    /**
     * Get the navigation icon for the resource
     */
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-storefront';
    }

    /**
     * Get the navigation group for the resource
     */
    public static function getNavigationGroup(): string
    {
        return 'System';
    }

    /**
     * Get the navigation label for the resource
     */
    public static function getNavigationLabel(): string
    {
        return 'Tenants';
    }

    /**
     * Get the model label for the resource
     */
    public static function getModelLabel(): string
    {
        return 'Tenant';
    }

    /**
     * Get the plural model label for the resource
     */
    public static function getPluralModelLabel(): string
    {
        return 'Tenants';
    }

    /**
     * Get the record title attribute
     */
    public static function getRecordTitleAttribute(): string
    {
        return 'name';
    }

    /**
     * Determine if the current user can access this resource
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the current user can view any records
     */
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    /**
     * Determine if the current user can create records
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the current user can edit a specific record
     */
    public static function canEdit($record): bool
    {
        return static::canAccess();
    }

    /**
     * Determine if the current user can delete a specific record
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Prevent deleting the default tenants
        $defaultTenants = [
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            '44444444-4444-4444-4444-444444444444',
        ];
        
        if (in_array($record->id, $defaultTenants)) {
            return false;
        }
        
        return $user->isAdmin();
    }

    /**
     * Determine if the current user can view a specific record
     */
    public static function canView($record): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'view' => ViewTenant::route('/{record}'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }

    /**
     * Get the navigation badge for the resource
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    /**
     * Get the color for the navigation badge
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    /**
     * Get the navigation sort order
     */
    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}