<?php

namespace App\Filament\Resources\LicensingAuthorityResource\Pages;

use App\Filament\Resources\LicensingAuthorityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicensingAuthority extends EditRecord
{
    protected static string $resource = LicensingAuthorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\DeleteAction::make(),
        ];
    }
}
