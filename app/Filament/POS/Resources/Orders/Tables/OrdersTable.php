<?php

namespace App\Filament\POS\Resources\Orders\Tables;

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

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $table
            ->columns([
                // ID column removed - internal identifier
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->icon('heroicon-o-receipt-refund'),
                
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Walk-in Customer'),
                
                TextColumn::make('cashier.full_name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('order_date')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn ($record) => $record->currency_code ?? 'KES')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'voided',
                        'info' => 'refunded',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'voided',
                        'heroicon-o-arrow-uturn-left' => 'refunded',
                    ])
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'success' => 'CASH',
                        'primary' => 'CARD',
                        'warning' => 'M_PESA',
                        'info' => 'BANK_TRANSFER',
                        'gray' => 'LOYALTY_POINTS',
                    ])
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', $state))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('receipt_number')
                    ->label('Receipt')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin || $isSupervisor)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'voided' => 'Voided',
                        'refunded' => 'Refunded',
                    ])
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'CASH' => 'Cash',
                        'CARD' => 'Card',
                        'M_PESA' => 'M-PESA',
                        'BANK_TRANSFER' => 'Bank Transfer',
                        'LOYALTY_POINTS' => 'Loyalty Points',
                    ])
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('has_customer')
                    ->label('Has Customer')
                    ->trueLabel('With Customer')
                    ->falseLabel('Walk-in')
                    ->placeholder('All')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('customer_id'),
                        false: fn ($query) => $query->whereNull('customer_id'),
                    ),
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
                        ->modalHeading('Delete Orders')
                        ->modalSubheading('Are you sure you want to delete the selected orders? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete orders'),
                ]),
            ])
            ->defaultSort('order_date', 'desc')
            ->searchable()
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}