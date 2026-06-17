<?php

namespace App\Filament\POS\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('tenant.name')
                    ->label('Tenant'),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('currency_code'),
                TextEntry::make('tax_rate')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
