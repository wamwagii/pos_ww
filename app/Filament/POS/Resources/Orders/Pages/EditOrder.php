<?php

namespace App\Filament\POS\Resources\Orders\Pages;

use App\Filament\POS\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    //Redirect to the orders list after editing an order
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
