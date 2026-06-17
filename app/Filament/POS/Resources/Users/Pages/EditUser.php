<?php

namespace App\Filament\POS\Resources\Users\Pages;

use App\Filament\POS\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    //Redirect to the users list after editing a user
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
