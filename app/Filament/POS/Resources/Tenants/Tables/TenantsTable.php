<?php

namespace App\Filament\POS\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        
        return $table
            ->columns([
                // ID column removed - internal identifier, not user-facing
                TextColumn::make('name')
                    ->label('Tenant Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-building-office')
                    ->copyable(),
                
                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-globe-alt'),
                
                TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->colors([
                        'primary' => 'Kenya',
                        'success' => 'South Africa',
                        'warning' => 'Uganda',
                        'info' => 'Tanzania',
                        'danger' => 'Rwanda',
                        'gray' => 'Nigeria',
                        'purple' => 'Ghana',
                        'blue' => 'Egypt',
                        'orange' => 'Morocco',
                        'pink' => 'Ethiopia',
                    ])
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
                
                TextColumn::make('currency_code')
                    ->label('Currency')
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
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country')
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
                    ->preload(),
                
                SelectFilter::make('currency_code')
                    ->label('Currency')
                    ->options([
                        'KES' => 'KES - Kenyan Shilling',
                        'USD' => 'USD - US Dollar',
                        'GBP' => 'GBP - British Pound',
                        'ZAR' => 'ZAR - South African Rand',
                        'UGX' => 'UGX - Ugandan Shilling',
                        'TZS' => 'TZS - Tanzanian Shilling',
                        'RWF' => 'RWF - Rwandan Franc',
                        'NGN' => 'NGN - Nigerian Naira',
                        'GHS' => 'GHS - Ghanaian Cedi',
                        'EGP' => 'EGP - Egyptian Pound',
                        'MAD' => 'MAD - Moroccan Dirham',
                        'ETB' => 'ETB - Ethiopian Birr',
                    ])
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->placeholder('All'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View Details'),
                EditAction::make()
                    ->label('Edit')
                    ->visible($isAdmin),
                \Filament\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->visible($isAdmin)
                    ->modalHeading('Delete Tenant')
                    ->modalSubheading('Are you sure you want to delete this tenant? This action cannot be undone and will delete all associated data.')
                    ->modalSubmitActionLabel('Yes, delete tenant'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->visible($isAdmin)
                        ->modalHeading('Delete Tenants')
                        ->modalSubheading('Are you sure you want to delete the selected tenants? This action cannot be undone and will delete all associated data.')
                        ->modalSubmitActionLabel('Yes, delete tenants'),
                ]),
            ])
            ->defaultSort('name')
            ->searchable()
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}