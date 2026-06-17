<?php

namespace App\Filament\POS\Resources\Orders\Pages;

use App\Filament\POS\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    //Redirect to the orders list after creating a new order
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
