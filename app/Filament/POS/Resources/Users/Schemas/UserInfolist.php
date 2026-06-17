<?php

namespace App\Filament\POS\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('tenant.name')
                    ->label('Tenant'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('role'),
                IconEntry::make('is_active')
                    ->boolean()
                    ->placeholder('-'),
                TextEntry::make('pin_code')
                    ->placeholder('-'),
                TextEntry::make('barcode')
                    ->placeholder('-'),
                TextEntry::make('fingerprint_hash')
                    ->placeholder('-'),
                TextEntry::make('biometric_device_id')
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
