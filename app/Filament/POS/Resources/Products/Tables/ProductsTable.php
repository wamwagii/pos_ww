<?php

namespace App\Filament\POS\Resources\Products\Tables;

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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $table
            ->columns([
                // ID column removed - internal identifier
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->copyable(),
                
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cube')
                    ->limit(30),
                
                TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->colors([
                        'primary' => 'Electronics',
                        'success' => 'Groceries',
                        'warning' => 'Clothing',
                        'info' => 'Pharmacy',
                        'danger' => 'Fast Food',
                        'gray' => 'Sports',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('unit_price')
                    ->label('Price')
                    ->money(fn ($record) => $record->currency_code ?? 'KES')
                    ->sortable()
                    ->weight('bold'),
                
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
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('tax_rate')
                    ->label('Tax')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('inventory.quantity')
                    ->label('Stock')
                    ->badge()
                    ->colors([
                        'danger' => fn ($state) => $state <= 0,
                        'warning' => fn ($state) => $state > 0 && $state <= 5,
                        'success' => fn ($state) => $state > 5,
                    ])
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->sortable(),
                
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin || $isSupervisor)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Electronics' => 'Electronics',
                        'Groceries' => 'Groceries',
                        'Clothing' => 'Clothing',
                        'Pharmacy' => 'Pharmacy',
                        'Fast Food' => 'Fast Food',
                        'Sports' => 'Sports',
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
                    ])
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->placeholder('All'),
                
                TernaryFilter::make('low_stock')
                    ->label('Low Stock')
                    ->trueLabel('Low Stock (≤ 5)')
                    ->falseLabel('In Stock')
                    ->placeholder('All')
                    ->query(fn ($query) => $query->whereHas('inventory', fn ($q) => $q->where('quantity', '<=', 5))),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View Details'),
                EditAction::make()
                    ->label('Edit')
                    ->visible(fn ($record) => $isAdmin || $isSupervisor),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->visible($isAdmin)
                        ->modalHeading('Delete Products')
                        ->modalSubheading('Are you sure you want to delete the selected products? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete products'),
                ]),
            ])
            ->defaultSort('name')
            ->searchable()
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}