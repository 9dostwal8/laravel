<?php

namespace App\Filament\Resources\LicensingAuthorityResource\Pages;

use App\Filament\Resources\LicensingAuthorityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicensingAuthorities extends ListRecords
{
    protected static string $resource = LicensingAuthorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
