<?php

namespace App\Filament\POS\Resources\Tenants\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ID is hidden - users should never see it
                Hidden::make('id'),
                
                Section::make('Tenant Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tenant Name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->prefixIcon('heroicon-o-building-office')
                            ->placeholder('Enter tenant name')
                            ->helperText('Enter the full name of the organization/tenant.'),
                        
                        TextInput::make('domain')
                            ->label('Domain')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-globe-alt')  // Changed from o-globe
                            ->placeholder('example.ke')
                            ->helperText('Unique domain identifier (e.g., nairobi-sports.ke)')
                            ->rule(['regex:/^[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,}$/']),
                    ])
                    ->columns(2),
                
                Section::make('Location & Currency')
                    ->icon('heroicon-o-map-pin')  // Changed from o-globe
                    ->schema([
                        // Country dropdown - only select from options
                        Select::make('country')
                            ->label('Country')
                            ->required()
                            ->options([
                                'Kenya' => '🇰🇪 Kenya',
                                'South Africa' => '🇿🇦 South Africa',
                                'Uganda' => '🇺🇬 Uganda',
                                'Tanzania' => '🇹🇿 Tanzania',
                                'Rwanda' => '🇷🇼 Rwanda',
                                'Nigeria' => '🇳🇬 Nigeria',
                                'Ghana' => '🇬🇭 Ghana',
                                'Egypt' => '🇪🇬 Egypt',
                                'Morocco' => '🇲🇦 Morocco',
                                'Ethiopia' => '🇪🇹 Ethiopia',
                            ])
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->helperText('Select the country where the tenant is located.')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $currencyMap = [
                                    'Kenya' => 'KES',
                                    'South Africa' => 'ZAR',
                                    'Uganda' => 'UGX',
                                    'Tanzania' => 'TZS',
                                    'Rwanda' => 'RWF',
                                    'Nigeria' => 'NGN',
                                    'Ghana' => 'GHS',
                                    'Egypt' => 'EGP',
                                    'Morocco' => 'MAD',
                                    'Ethiopia' => 'ETB',
                                ];
                                
                                if ($state && isset($currencyMap[$state])) {
                                    $set('currency_code', $currencyMap[$state]);
                                }
                            }),
                        
                        // Currency - auto-selected based on country, hidden from user
                        Hidden::make('currency_code'),
                        
                        // Display-only currency field - shows the auto-selected currency
                        Placeholder::make('currency_display')
                            ->label('Default Currency')
                            ->content(function ($get) {
                                $currencyMap = [
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
                                ];
                                $currencyCode = $get('currency_code');
                                return $currencyCode && isset($currencyMap[$currencyCode]) 
                                    ? $currencyMap[$currencyCode] 
                                    : 'Please select a country first';
                            })
                            ->extraAttributes(['class' => 'font-semibold text-primary-600'])
                            ->helperText('Currency is automatically set based on the selected country and cannot be changed.'),
                    ])
                    ->columns(2),
                
                Section::make('Status')
                    ->icon('heroicon-o-circle-stack')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive tenants will not be able to access the system.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}