<?php

namespace App\Filament\POS\Resources\Tenants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        
        return $schema
            ->components([
                Section::make('Tenant Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        // ID is NOT displayed - internal identifier only
                        TextEntry::make('name')
                            ->label('Tenant Name')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),
                        
                        TextEntry::make('domain')
                            ->label('Domain')
                            ->icon('heroicon-o-globe-alt')  // Changed from o-globe
                            ->copyable(),
                        
                        TextEntry::make('country')
                            ->label('Country')
                            ->icon('heroicon-o-map-pin')
                            ->badge()
                            ->formatStateUsing(function ($state) {
                                $flags = [
                                    'Kenya' => '🇰🇪',
                                    'South Africa' => '🇿🇦',
                                    'Uganda' => '🇺🇬',
                                    'Tanzania' => '🇹🇿',
                                    'Rwanda' => '🇷🇼',
                                    'Nigeria' => '🇳🇬',
                                    'Ghana' => '🇬🇭',
                                    'Egypt' => '🇪🇬',
                                    'Morocco' => '🇲🇦',
                                    'Ethiopia' => '🇪🇹',
                                ];
                                return ($flags[$state] ?? '') . ' ' . $state;
                            }),
                        
                        TextEntry::make('currency_code')
                            ->label('Default Currency')
                            ->badge()
                            ->colors([
                                'primary' => 'KES',
                                'success' => 'USD',
                                'warning' => 'GBP',
                                'info' => 'ZAR',
                                'danger' => 'UGX',
                                'gray' => 'TZS',
                                'purple' => 'RWF',
                                'blue' => 'NGN',
                                'orange' => 'GHS',
                                'pink' => 'EGP',
                                'teal' => 'MAD',
                                'indigo' => 'ETB',
                            ])
                            ->formatStateUsing(function ($state) {
                                $currencyNames = [
                                    'KES' => 'Kenyan Shilling (KSh)',
                                    'USD' => 'US Dollar ($)',
                                    'GBP' => 'British Pound (£)',
                                    'ZAR' => 'South African Rand (R)',
                                    'UGX' => 'Ugandan Shilling (USh)',
                                    'TZS' => 'Tanzanian Shilling (TSh)',
                                    'RWF' => 'Rwandan Franc (FRw)',
                                    'NGN' => 'Nigerian Naira (₦)',
                                    'GHS' => 'Ghanaian Cedi (₵)',
                                    'EGP' => 'Egyptian Pound (E£)',
                                    'MAD' => 'Moroccan Dirham (DH)',
                                    'ETB' => 'Ethiopian Birr (Br)',
                                ];
                                return $state . ' - ' . ($currencyNames[$state] ?? $state);
                            }),
                        
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        TextEntry::make('users_count')
                            ->label('Total Users')
                            ->counts('users')
                            ->badge()
                            ->color('primary'),
                        
                        TextEntry::make('products_count')
                            ->label('Total Products')
                            ->counts('products')
                            ->badge()
                            ->color('success'),
                        
                        TextEntry::make('orders_count')
                            ->label('Total Orders')
                            ->counts('orders')
                            ->badge()
                            ->color('warning'),
                        
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y H:i:s')
                            ->icon('heroicon-o-calendar'),
                        
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y H:i:s')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(2),
            ]);
    }
}