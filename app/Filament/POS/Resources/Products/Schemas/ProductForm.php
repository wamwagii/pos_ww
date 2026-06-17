<?php

namespace App\Filament\POS\Resources\Products\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductForm
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
                
                // Tenant - auto-set or only visible to admins/supervisors
                Hidden::make('tenant_id')
                    ->default(fn () => filament()->getTenant()?->getKey()),
                
                Section::make('Product Information')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        // Tenant - only visible to admins/supervisors for assignment
                        Select::make('tenant_id')
                            ->label('Tenant')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->visible($isAdmin || $isSupervisor)
                            ->prefixIcon('heroicon-o-building-office')
                            ->helperText('The tenant this product belongs to.'),
                        
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, table: 'products')
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('PRD-001')
                            ->helperText('Unique product identifier.'),
                        
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->prefixIcon('heroicon-o-cube')
                            ->placeholder('Product Name')
                            ->helperText('The display name of the product.'),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Detailed product description...')
                            ->helperText('Detailed product description.'),
                    ])
                    ->columns(2),
                
                Section::make('Pricing & Details')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('category')
                            ->label('Category')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('e.g., Electronics, Groceries')
                            ->helperText('Product category for organization.'),
                        
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Price per unit.'),
                        
                        Select::make('currency_code')
                            ->label('Currency')
                            ->options([
                                'KES' => '🇰🇪 KES - Kenyan Shilling (KSh)',
                                'USD' => '🇺🇸 USD - US Dollar ($)',
                                'GBP' => '🇬🇧 GBP - British Pound (£)',
                                'ZAR' => '🇿🇦 ZAR - South African Rand (R)',
                                'UGX' => '🇺🇬 UGX - Ugandan Shilling (USh)',
                                'TZS' => '🇹🇿 TZS - Tanzanian Shilling (TSh)',
                                'RWF' => '🇷🇼 RWF - Rwandan Franc (FRw)',
                                'NGN' => '🇳🇬 NGN - Nigerian Naira (₦)',
                                'GHS' => '🇬🇭 GHS - Ghanaian Cedi (₵)',
                                'EGP' => '🇪🇬 EGP - Egyptian Pound (E£)',
                                'MAD' => '🇲🇦 MAD - Moroccan Dirham (DH)',
                                'ETB' => '🇪🇹 ETB - Ethiopian Birr (Br)',
                            ])
                            ->required()
                            ->default('KES')
                            ->searchable()
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->helperText('Currency for this product.'),
                        
                        TextInput::make('tax_rate')
                            ->label('Tax Rate')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('16')
                            ->helperText('Tax percentage applied to this product.'),
                    ])
                    ->columns(2),
                
                Section::make('Inventory & Status')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive products will not appear in POS and cannot be sold.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}