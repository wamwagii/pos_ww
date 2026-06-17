<?php

namespace App\Filament\POS\Resources\Orders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $schema
            ->components([
                Section::make('Order Summary')
                    ->icon('heroicon-o-receipt-refund')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order Number')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),
                        
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'warning' => 'pending',
                                'success' => 'completed',
                                'danger' => 'voided',
                                'info' => 'refunded',
                            ]),
                        
                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money(fn ($record) => $record->currency_code ?? 'KES')
                            ->size('lg')
                            ->weight('bold'),
                        
                        TextEntry::make('order_date')
                            ->label('Order Date')
                            ->dateTime('M d, Y H:i:s'),
                        
                        TextEntry::make('customer.full_name')
                            ->label('Customer')
                            ->placeholder('Walk-in Customer'),
                        
                        TextEntry::make('cashier.full_name')
                            ->label('Cashier'),
                        
                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->formatStateUsing(fn ($state) => str_replace('_', ' ', $state)),
                        
                        TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->colors([
                                'warning' => 'pending',
                                'success' => 'paid',
                                'danger' => 'failed',
                                'info' => 'refunded',
                            ]),
                        
                        TextEntry::make('amount_tendered')
                            ->label('Amount Tendered')
                            ->money(fn ($record) => $record->currency_code ?? 'KES'),
                        
                        TextEntry::make('change_due')
                            ->label('Change Due')
                            ->money(fn ($record) => $record->currency_code ?? 'KES'),
                        
                        TextEntry::make('receipt_number')
                            ->label('Receipt Number')
                            ->copyable(),
                        
                        TextEntry::make('tenant.name')
                            ->label('Tenant')
                            ->visible($isAdmin || $isSupervisor),
                        
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Cash Denominations')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('cash_denominations')
                            ->label('Cash Breakdown')
                            ->formatStateUsing(function ($state) {
                                if (is_array($state) && !empty($state)) {
                                    $html = '<div class="space-y-1">';
                                    foreach ($state as $denom => $count) {
                                        $html .= "<div class='flex justify-between items-center'>
                                                    <span class='font-medium'>" . number_format($denom) . "</span>
                                                    <span class='text-gray-500'>× {$count}</span>
                                                 </div>";
                                    }
                                    $html .= '</div>';
                                    return $html;
                                }
                                return 'N/A';
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->payment_method === 'CASH' && !empty($record->cash_denominations)),
                
                Section::make('Void Information')
                    ->icon('heroicon-o-no-symbol')
                    ->schema([
                        TextEntry::make('voided_at')
                            ->label('Voided At')
                            ->dateTime('M d, Y H:i:s'),
                        
                        TextEntry::make('voidedBy.full_name')
                            ->label('Voided By'),
                        
                        TextEntry::make('void_reason')
                            ->label('Void Reason')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->status === 'voided'),
            ]);
    }
}