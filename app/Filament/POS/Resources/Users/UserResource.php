<?php

namespace App\Filament\POS\Resources\Users;

use App\Filament\POS\Resources\Users\Pages\CreateUser;
use App\Filament\POS\Resources\Users\Pages\EditUser;
use App\Filament\POS\Resources\Users\Pages\ListUsers;
use App\Filament\POS\Resources\Users\Pages\ViewUser;
use App\Filament\POS\Resources\Users\Schemas\UserForm;
use App\Filament\POS\Resources\Users\Schemas\UserInfolist;
use App\Filament\POS\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): string
    {
        return 'User Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Users';
    }

    public static function getModelLabel(): string
    {
        return 'User';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Users';
    }

    public static function getRecordTitleAttribute(): string
    {
        return 'full_name';
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isSupervisor());
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($record->isAdmin() && !$user->isAdmin()) {
            return false;
        }
        
        if ($record->id === $user->id) {
            return $user->isAdmin();
        }
        
        if ($user->isSupervisor()) {
            return !$record->isAdmin();
        }
        
        return $user->isAdmin();
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($record->id === $user->id) {
            return false;
        }
        
        return $user->isAdmin();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}