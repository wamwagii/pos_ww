<?php

namespace App\Filament\POS\Resources\Tenants\Pages;

use App\Filament\POS\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    //Redirect to the tenants list after creating a new tenant
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');

    }
}
