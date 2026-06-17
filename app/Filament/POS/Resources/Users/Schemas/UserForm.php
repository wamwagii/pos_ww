<?php

namespace App\Filament\POS\Resources\Users\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        $isEdit = $schema->getModel() !== null;
        
        return $schema
            ->components([
                // ID is hidden
                Hidden::make('id'),
                
                Section::make('Personal Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, table: 'users')
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('user@example.com')
                            ->helperText('This will be used for login.'),
                        
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(100)
                            ->autofocus()
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('John'),
                        
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Doe'),
                        
                        TextInput::make('password_hash')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->helperText(fn (string $context): string => 
                                $context === 'create' 
                                    ? 'Enter a strong password (minimum 8 characters).' 
                                    : 'Leave blank to keep the current password.'
                            )
                            ->rule(fn (string $context): array => 
                                $context === 'create' 
                                    ? ['min:8'] 
                                    : []
                            ),
                        
                        // Role field - only visible to admins
                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'cashier' => '🛒 Cashier',
                                'supervisor' => '👔 Supervisor',
                                'admin' => '🔐 Admin',
                                'manager' => '📋 Manager',
                            ])
                            ->required()
                            ->default('cashier')
                            ->prefixIcon('heroicon-o-user-group')
                            ->visible($isAdmin || $isSupervisor)
                            ->helperText('Determines what permissions the user has.'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->visible($isAdmin || $isSupervisor)
                            ->helperText('Inactive users cannot log in.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                // Tenant field - only visible to admins
                Section::make('Tenant Assignment')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Tenant')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office')
                            ->helperText('The organization this user belongs to.')
                            ->visible($isAdmin),
                    ])
                    ->visible($isAdmin)
                    ->columns(1),
                
                // Security Section - only visible to admins and supervisors
                Section::make('Security & Access')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('pin_code')
                            ->label('PIN Code')
                            ->maxLength(6)
                            ->password()
                            ->revealable()
                            ->placeholder('1234')
                            ->helperText('4-6 digit PIN for supervisor authorization.')
                            ->rule(['digits_between:4,6'])
                            ->visible($isAdmin || $isSupervisor),
                        
                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true, table: 'users')
                            ->placeholder('EMP-001')
                            ->helperText('Employee barcode for scanning.')
                            ->visible($isAdmin || $isSupervisor),
                    ])
                    ->columns(2)
                    ->visible($isAdmin || $isSupervisor),
                
                // Biometric Section - only visible to admins
                Section::make('Biometric Information')
                    ->icon('heroicon-o-finger-print')
                    ->schema([
                        TextInput::make('fingerprint_hash')
                            ->label('Fingerprint')
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('Fingerprint data is stored securely and cannot be edited directly.')
                            ->dehydrated(false) // Don't save this field
                            ->placeholder('Registered: No'),
                        
                        TextInput::make('biometric_device_id')
                            ->label('Biometric Device ID')
                            ->maxLength(100)
                            ->placeholder('DEVICE-001')
                            ->helperText('ID of the biometric device assigned to this user.')
                            ->visible($isAdmin),
                    ])
                    ->columns(2)
                    ->visible($isAdmin),
                
                // Timestamps - view only
                Section::make('Metadata')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Created')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible($isEdit && $isAdmin),
                        
                        TextInput::make('updated_at')
                            ->label('Last Updated')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible($isEdit && $isAdmin),
                    ])
                    ->columns(2)
                    ->visible($isEdit && $isAdmin),
            ]);
    }
}