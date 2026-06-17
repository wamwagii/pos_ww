<?php

namespace App\Filament\POS\Resources\Products\Pages;

use App\Filament\POS\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

     //Redirect to the products list after editing a product
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');

    }
}
