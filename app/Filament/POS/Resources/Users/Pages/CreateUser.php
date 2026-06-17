<?php

namespace App\Filament\POS\Resources\Users\Pages;

use App\Filament\POS\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    //Redirect to the users list after creating a new user
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
