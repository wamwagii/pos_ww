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
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $recordTitleAttribute = 'product.name';

    public function form(Schema $schema): Schema
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        $isSupervisor = Auth::user()?->isSupervisor() ?? false;
        
        return $schema
            ->components([
                // Hidden fields
                Hidden::make('id'),
                Hidden::make('tenant_id')
                    ->default(fn () => filament()->getTenant()?->getKey()),
                
                Section::make('Product Information')
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Product')
                            ->prefixIcon('heroicon-o-cube'),
                        
                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Number of units for this product.'),
                    ])
                    ->columns(2),
                
                Section::make('Pricing Details')
                    ->schema([
                        TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->helperText('Price per unit.'),
                        
                        TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->helperText('Quantity × Unit Price'),
                        
                        TextInput::make('tax_amount')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->helperText('Tax amount for this item.'),
                    ])
                    ->columns(2),
                
                Section::make('Discount Information')
                    ->schema([
                        TextInput::make('discount_amount')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('../../currency_code') ?? 'KES')
                            ->helperText('Discount amount for this item.'),
                        
                        TextInput::make('discount_percentage')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Discount percentage for this item.'),
                        
                        Textarea::make('discount_reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Reason for discount...')
                            ->helperText('Optional: Reason for applying discount.'),
                    ])
                    ->columns(2),
                
                Section::make('Removal Information')
                    ->schema([
                        Toggle::make('is_removed')
                            ->label('Item Removed')
                            ->default(false)
                            ->helperText('Mark this item as removed from the order.')
                            ->visible($isAdmin || $isSupervisor),
                        
                        DateTimePicker::make('removed_at')
                            ->label('Removed At')
                            ->disabled()
                            ->visible($isAdmin || $isSupervisor),
                        
                        Select::make('removed_by')
                            ->label('Removed By')
                            ->relationship('removedBy', 'email')
                            ->searchable()
                            ->disabled()
                            ->visible($isAdmin || $isSupervisor),
                        
                        Textarea::make('removal_reason')
                            ->label('Removal Reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Reason for removing this item...')
                            ->helperText('Required when marking an item as removed.')
                            ->requiredIf('is_removed', true)
                            ->visible($isAdmin || $isSupervisor),
                        
                        TextInput::make('removal_authorization_id')
                            ->label('Authorization ID')
                            ->disabled()
                            ->visible($isAdmin),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn ($get) => !$get('is_removed')),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isSupervisor = $user?->isSupervisor() ?? false;
        
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                // ID column removed - internal identifier, not user-facing
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-cube'),
                
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable()
                    ->weight('bold')
                    ->alignCenter(),
                
                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money(fn ($record) => $record->order->currency_code ?? 'KES')
                    ->sortable(),
                
                TextColumn::make('total_price')
                    ->label('Total')
                    ->money(fn ($record) => $record->order->currency_code ?? 'KES')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('tax_amount')
                    ->label('Tax')
                    ->money(fn ($record) => $record->order->currency_code ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money(fn ($record) => $record->order->currency_code ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_removed')
                    ->label('Removed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                
                TextColumn::make('removal_reason')
                    ->label('Removal Reason')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('removed_at')
                    ->label('Removed At')
                    ->dateTime('M d, Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('removedBy.full_name')
                    ->label('Removed By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_removed')
                    ->label('Removed Items')
                    ->trueLabel('Removed Only')
                    ->falseLabel('Active Only')
                    ->placeholder('All Items')
                    ->queries(
                        true: fn ($query) => $query->where('is_removed', true),
                        false: fn ($query) => $query->where('is_removed', false),
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Item')
                    ->visible($isAdmin || $isSupervisor)
                    ->modalHeading('Add Order Item')
                    ->modalSubmitActionLabel('Add Item'),
            ])
            ->actions([
                EditAction::make()
                    ->visible($isAdmin || $isSupervisor)
                    ->modalHeading('Edit Order Item'),
                
                DeleteAction::make()
                    ->visible($isAdmin)
                    ->modalHeading('Delete Order Item')
                    ->modalSubmitActionLabel('Delete Item'),
                
                \Filament\Actions\Action::make('toggle_removed')
                    ->label(fn ($record) => $record->is_removed ? '🔄 Restore' : '🗑️ Remove')
                    ->color(fn ($record) => $record->is_removed ? 'success' : 'danger')
                    ->icon(fn ($record) => $record->is_removed ? 'heroicon-o-arrow-path' : 'heroicon-o-x-mark')
                    ->visible($isAdmin || $isSupervisor)
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_removed ? 'Restore Item' : 'Remove Item')
                    ->modalSubheading(fn ($record) => $record->is_removed 
                        ? 'Are you sure you want to restore this item to the order?' 
                        : 'Are you sure you want to remove this item from the order?')
                    ->modalSubmitActionLabel(fn ($record) => $record->is_removed ? 'Yes, Restore' : 'Yes, Remove')
                    ->action(function ($record) {
                        $record->update([
                            'is_removed' => !$record->is_removed,
                            'removed_at' => $record->is_removed ? null : now(),
                            'removed_by' => $record->is_removed ? null : auth()->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible($isAdmin)
                        ->modalHeading('Delete Order Items')
                        ->modalSubmitActionLabel('Delete Items'),
                    
                    \Filament\Actions\BulkAction::make('bulk_remove')
                        ->label('Remove Selected')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->visible($isAdmin || $isSupervisor)
                        ->requiresConfirmation()
                        ->modalHeading('Remove Order Items')
                        ->modalSubheading('Are you sure you want to remove the selected items from the order?')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_removed' => true,
                                    'removed_at' => now(),
                                    'removed_by' => auth()->id(),
                                ]);
                            }
                        }),
                    
                    \Filament\Actions\BulkAction::make('bulk_restore')
                        ->label('Restore Selected')
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->visible($isAdmin || $isSupervisor)
                        ->requiresConfirmation()
                        ->modalHeading('Restore Order Items')
                        ->modalSubheading('Are you sure you want to restore the selected items to the order?')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_removed' => false,
                                    'removed_at' => null,
                                    'removed_by' => null,
                                ]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}