<?php

namespace App\Filament\POS\Resources\Products;

use App\Filament\POS\Resources\Products\Pages\CreateProduct;
use App\Filament\POS\Resources\Products\Pages\EditProduct;
use App\Filament\POS\Resources\Products\Pages\ListProducts;
use App\Filament\POS\Resources\Products\Pages\ViewProduct;
use App\Filament\POS\Resources\Products\Schemas\ProductForm;
use App\Filament\POS\Resources\Products\Schemas\ProductInfolist;
use App\Filament\POS\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationGroup(): string
    {
        return 'Catalog';
    }

    public static function getNavigationLabel(): string
    {
        return 'Products';
    }

    public static function getModelLabel(): string
    {
        return 'Product';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Products';
    }

    public static function getRecordTitleAttribute(): string
    {
        return 'name';
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isSupervisor() || $user->isCashier());
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isSupervisor());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isSupervisor());
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }
}