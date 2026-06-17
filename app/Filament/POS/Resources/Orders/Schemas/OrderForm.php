<?php

namespace App\Filament\POS\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        // Check if we're in edit mode by checking if the schema has a model
        $isEdit = $schema->getModel() !== null;
        
        return $schema
            ->components([
                // Hidden fields
                Hidden::make('id'),
                Hidden::make('tenant_id')
                    ->default(fn () => filament()->getTenant()?->getKey()),
                
                Section::make('Order Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->disabled($isEdit)
                            ->prefixIcon('heroicon-o-receipt-refund')
                            ->helperText($isEdit ? 'Order number cannot be changed.' : 'Auto-generated if left blank.')
                            ->placeholder('ORD-' . date('Ymd') . '-0001'),
                        
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->preload()
                            ->placeholder('Walk-in Customer')
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Select an existing customer or leave blank for walk-in.'),
                        
                        Select::make('cashier_id')
                            ->label('Cashier')
                            ->relationship('cashier', 'email')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->id())
                            ->prefixIcon('heroicon-o-user-group')
                            ->disabled($isEdit && !$isAdmin && !$isSupervisor),
                        
                        DateTimePicker::make('order_date')
                            ->label('Order Date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->prefixIcon('heroicon-o-calendar'),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'voided' => 'Voided',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('pending')
                            ->prefixIcon('heroicon-o-circle-stack')
                            ->visible($isAdmin || $isSupervisor),
                        
                        Select::make('currency_code')
                            ->label('Currency')
                            ->options([
                                'KES' => 'KES - Kenyan Shilling (KSh)',
                                'USD' => 'USD - US Dollar ($)',
                                'GBP' => 'GBP - British Pound (£)',
                                'ZAR' => 'ZAR - South African Rand (R)',
                                'UGX' => 'UGX - Ugandan Shilling (USh)',
                                'TZS' => 'TZS - Tanzanian Shilling (TSh)',
                                'RWF' => 'RWF - Rwandan Franc (FRw)',
                            ])
                            ->required()
                            ->default('KES')
                            ->searchable()
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->helperText('Currency used for this order.'),
                    ])
                    ->columns(2),
                
                Section::make('Financial Details')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->helperText('Total before tax and discounts.'),
                        
                        TextInput::make('tax_amount')
                            ->label('Tax')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->helperText('Tax amount for this order.'),
                        
                        TextInput::make('discount_amount')
                            ->label('Discount')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->helperText('Discount applied to this order.'),
                        
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->disabled()
                            ->helperText('Auto-calculated: Subtotal + Tax - Discount'),
                    ])
                    ->columns(2),
                
                Section::make('Payment Information')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'CASH' => '💵 Cash',
                                'CARD' => '💳 Card',
                                'M_PESA' => '📱 M-PESA',
                                'BANK_TRANSFER' => '🏦 Bank Transfer',
                                'LOYALTY_POINTS' => '⭐ Loyalty Points',
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->prefixIcon('heroicon-o-credit-card')
                            ->helperText('Select the payment method used.'),
                        
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => '🟡 Pending',
                                'paid' => '🟢 Paid',
                                'failed' => '🔴 Failed',
                                'refunded' => '🔵 Refunded',
                            ])
                            ->required()
                            ->default('pending')
                            ->prefixIcon('heroicon-o-check-circle'),
                        
                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('Transaction ID, M-PESA code, or reference number.'),
                        
                        // Cash-specific fields
                        TextInput::make('amount_tendered')
                            ->label('Amount Tendered')
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->live()
                            ->helperText('Amount given by customer.')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                        
                        TextInput::make('change_due')
                            ->label('Change Due')
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('currency_code') ?? 'KES')
                            ->minValue(0)
                            ->disabled()
                            ->helperText('Auto-calculated: Amount Tendered - Total Amount')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH'),
                        
                        // Cash denominations with better UX
                        KeyValue::make('cash_denominations')
                            ->label('Cash Breakdown')
                            ->keyLabel('Denomination')
                            ->valueLabel('Count')
                            ->addActionLabel('Add Denomination')
                            ->helperText('Enter each denomination and the number of notes/coins received.')
                            ->visible(fn ($get) => $get('payment_method') === 'CASH')
                            ->columnSpanFull()
                            ->default([]),
                        
                        // Card-specific fields
                        TextInput::make('card_last_four')
                            ->label('Card Last 4 Digits')
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('Last 4 digits of the card.')
                            ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                        
                        TextInput::make('card_type')
                            ->label('Card Type')
                            ->placeholder('Visa, Mastercard, etc.')
                            ->helperText('Type of card used.')
                            ->visible(fn ($get) => $get('payment_method') === 'CARD'),
                        
                        // M-PESA-specific fields
                        TextInput::make('mpesa_code')
                            ->label('M-PESA Code')
                            ->maxLength(50)
                            ->placeholder('ABC123XYZ')
                            ->helperText('M-PESA transaction code.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                        
                        TextInput::make('mpesa_phone')
                            ->label('M-PESA Phone')
                            ->maxLength(20)
                            ->placeholder('0712345678')
                            ->helperText('Phone number used for M-PESA payment.')
                            ->visible(fn ($get) => $get('payment_method') === 'M_PESA'),
                        
                        // Bank Transfer-specific fields
                        TextInput::make('bank_reference')
                            ->label('Bank Reference')
                            ->maxLength(100)
                            ->helperText('Bank transaction reference number.')
                            ->visible(fn ($get) => $get('payment_method') === 'BANK_TRANSFER'),
                    ])
                    ->columns(2),
                
                Section::make('Additional Details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('receipt_number')
                            ->label('Receipt Number')
                            ->maxLength(50)
                            ->prefixIcon('heroicon-o-document-duplicate')
                            ->helperText('Auto-generated on payment completion.'),
                        
                        DateTimePicker::make('receipt_printed_at')
                            ->label('Receipt Printed At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->prefixIcon('heroicon-o-printer')
                            ->disabled(),
                        
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Any additional notes about this order...')
                            // Remove prefixIcon - not supported on Textarea
                            ->extraAttributes(['class' => 'textarea-with-icon'])
                            ->helperText('Add any additional notes about this order.'),
                    ])
                    ->columns(2),
                
                // Void section - only visible when status is voided
                Section::make('Void Information')
                    ->icon('heroicon-o-no-symbol')
                    ->schema([
                        DateTimePicker::make('voided_at')
                            ->label('Voided At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->disabled(),
                        
                        Select::make('voided_by')
                            ->label('Voided By')
                            ->relationship('voidedBy', 'email')
                            ->searchable()
                            ->disabled()
                            ->prefixIcon('heroicon-o-user'),
                        
                        Textarea::make('void_reason')
                            ->label('Void Reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Reason for voiding this order...'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('status') === 'voided')
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}