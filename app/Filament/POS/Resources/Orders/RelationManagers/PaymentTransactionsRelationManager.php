<?php

namespace App\Filament\POS\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentTransactions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $schema
            ->components([
                // Hidden fields
                Hidden::make('id'),
                Hidden::make('tenant_id')
                    ->default(fn () => filament()->getTenant()?->getKey()),
                
                Section::make('Transaction Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options([
                                'PAYMENT' => '💳 Payment',
                                'REFUND' => '↩️ Refund',
                                'VOID' => '🚫 Void',
                            ])
                            ->required()
                            ->default('PAYMENT')
                            ->prefixIcon('heroicon-o-arrow-path'),
                        
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'CASH' => '💵 Cash',
                                'CARD' => '💳 Card',
                                'M_PESA' => '📱 M-PESA',
                                'BANK_TRANSFER' => '🏦 Bank Transfer',
                                'LOYALTY_POINTS' => '⭐ Loyalty Points',
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-credit-card'),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => '⏳ Pending',
                                'completed' => '✅ Completed',
                                'failed' => '❌ Failed',
                                'refunded' => '🔄 Refunded',
                            ])
                            ->required()
                            ->default('pending')
                            ->prefixIcon('heroicon-o-check-circle'),
                    ])
                    ->columns(2),
                
                Section::make('Amount Details')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->minValue(0)
                            ->helperText('Transaction amount.'),
                        
                        Select::make('currency_code')
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
                            ->required()
                            ->default('KES')
                            ->searchable()
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        
                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->default(1.0)
                            ->step(0.0001)
                            ->prefix('1 USD = ')
                            ->helperText('Exchange rate if currency is different from base.'),
                    ])
                    ->columns(2),
                
                Section::make('Cash Details')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('cash_tendered')
                            ->label('Cash Tendered')
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->minValue(0)
                            ->helperText('Amount of cash given by customer.')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                        
                        TextInput::make('cash_change')
                            ->label('Cash Change')
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->minValue(0)
                            ->helperText('Change returned to customer.')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                        
                        Textarea::make('cash_denominations')
                            ->label('Cash Denominations')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('e.g., 1000: 2, 500: 1, 200: 3')
                            ->helperText('Breakdown of cash denominations received.')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                
                Section::make('Card Details')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        TextInput::make('card_reference')
                            ->label('Card Reference')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('Card transaction reference number.')
                            ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                        
                        TextInput::make('card_last_four')
                            ->label('Card Last 4 Digits')
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('Last 4 digits of the card.')
                            ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                        
                        TextInput::make('card_authorization_code')
                            ->label('Card Authorization Code')
                            ->maxLength(50)
                            ->helperText('Authorization code from card processor.')
                            ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                
                Section::make('M-PESA Details')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('mpesa_code')
                            ->label('M-PESA Code')
                            ->maxLength(50)
                            ->placeholder('ABC123XYZ')
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('M-PESA transaction code.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                        
                        TextInput::make('mpesa_phone')
                            ->label('M-PESA Phone')
                            ->maxLength(20)
                            ->placeholder('0712345678')
                            ->tel()
                            ->helperText('Phone number used for M-PESA payment.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                        
                        TextInput::make('mpesa_result_code')
                            ->label('M-PESA Result Code')
                            ->maxLength(20)
                            ->helperText('Result code from M-PESA response.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                        
                        Textarea::make('mpesa_result_description')
                            ->label('M-PESA Result Description')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->helperText('Result description from M-PESA response.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                
                Section::make('Bank Transfer Details')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextInput::make('bank_reference')
                            ->label('Bank Reference')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('Bank transaction reference number.')
                            ->visible(fn ($get) => $get('payment_method') === 'BANK_TRANSFER'),
                        
                        TextInput::make('bank_account_last_four')
                            ->label('Bank Account Last 4')
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('Last 4 digits of the bank account.')
                            ->visible(fn ($get) => $get('payment_method') === 'BANK_TRANSFER'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('payment_method') === 'BANK_TRANSFER'),
                
                Section::make('Failure Details')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Textarea::make('failure_reason')
                            ->label('Failure Reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Reason for transaction failure...')
                            ->helperText('Provide details if the transaction failed.')
                            ->visible(fn ($get) => $get('status') === 'failed'),
                    ])
                    ->visible(fn ($get) => $get('status') === 'failed'),
                
                Section::make('Processing Information')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        DateTimePicker::make('processed_at')
                            ->label('Processed At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->prefixIcon('heroicon-o-calendar')
                            ->helperText('Date and time when the transaction was processed.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // ID column removed - internal identifier
                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'PAYMENT',
                        'warning' => 'REFUND',
                        'danger' => 'VOID',
                    ])
                    ->icons([
                        'heroicon-o-credit-card' => 'PAYMENT',
                        'heroicon-o-arrow-uturn-left' => 'REFUND',
                        'heroicon-o-x-circle' => 'VOID',
                    ])
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->colors([
                        'success' => 'CASH',
                        'primary' => 'CARD',
                        'warning' => 'M_PESA',
                        'info' => 'BANK_TRANSFER',
                        'gray' => 'LOYALTY_POINTS',
                    ])
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', $state))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency_code ?? 'KES')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ])
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('mpesa_code')
                    ->label('M-PESA Code')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->options([
                        'PAYMENT' => 'Payment',
                        'REFUND' => 'Refund',
                        'VOID' => 'Void',
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
                
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('has_mpesa')
                    ->label('Has M-PESA Code')
                    ->trueLabel('With M-PESA')
                    ->falseLabel('Without M-PESA')
                    ->placeholder('All')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('mpesa_code'),
                        false: fn ($query) => $query->whereNull('mpesa_code'),
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Transaction')
                    ->visible($isAdmin || $isSupervisor)
                    ->modalHeading('Add Payment Transaction')
                    ->modalSubmitActionLabel('Add Transaction'),
            ])
            ->actions([
                EditAction::make()
                    ->visible($isAdmin || $isSupervisor)
                    ->modalHeading('Edit Payment Transaction'),
                
                DeleteAction::make()
                    ->visible($isAdmin)
                    ->modalHeading('Delete Transaction')
                    ->modalSubmitActionLabel('Delete Transaction'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible($isAdmin)
                        ->modalHeading('Delete Transactions')
                        ->modalSubmitActionLabel('Delete Transactions'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}