<?php

namespace App\Filament\Resources\OfficeReportsResource\Pages;

use App\Filament\Resources\OfficeReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeReports extends ListRecords
{
    protected static string $resource = OfficeReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
