<?php

namespace App\Filament\POS\Resources\Products\Pages;

use App\Filament\POS\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

     //Redirect to the products list after creating a new product
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');

    }
}
