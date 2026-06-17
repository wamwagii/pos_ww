<?php

namespace App\Filament\POS\Resources\Orders;

use App\Filament\POS\Resources\Orders\Pages\CreateOrder;
use App\Filament\POS\Resources\Orders\Pages\EditOrder;
use App\Filament\POS\Resources\Orders\Pages\ListOrders;
use App\Filament\POS\Resources\Orders\Pages\ViewOrder;
use App\Filament\POS\Resources\Orders\Schemas\OrderForm;
use App\Filament\POS\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\POS\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    /**
     * Get the navigation icon for the resource
     */
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    /**
     * Get the navigation group for the resource
     */
    public static function getNavigationGroup(): string
    {
        return 'Sales';
    }

    /**
     * Get the navigation label for the resource
     */
    public static function getNavigationLabel(): string
    {
        return 'Orders';
    }

    /**
     * Get the model label for the resource
     */
    public static function getModelLabel(): string
    {
        return 'Order';
    }

    /**
     * Get the plural model label for the resource
     */
    public static function getPluralModelLabel(): string
    {
        return 'Orders';
    }

    /**
     * Get the record title attribute
     */
    public static function getRecordTitleAttribute(): string
    {
        return 'order_number';
    }

    /**
     * Determine if the current user can access this resource
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isSupervisor() || $user->isCashier());
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
        return $user && ($user->isAdmin() || $user->isSupervisor() || $user->isCashier());
    }

    /**
     * Determine if the current user can edit a specific record
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Only admins and supervisors can edit orders
        return $user->isAdmin() || $user->isSupervisor();
    }

    /**
     * Determine if the current user can delete a specific record
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Only admins can delete orders
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
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\POS\Resources\Orders\RelationManagers\OrderItemsRelationManager::class,
            \App\Filament\POS\Resources\Orders\RelationManagers\PaymentTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
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
        return 'warning';
    }

    /**
     * Get the navigation sort order
     */
    public static function getNavigationSort(): ?int
    {
        return 4;
    }
}