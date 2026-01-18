<?php

namespace App\Filament\Resources\OfficeReportsResource\Pages;

use App\Filament\Resources\OfficeReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficeReports extends EditRecord
{
    protected static string $resource = OfficeReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
