<?php

namespace App\Filament\Resources\Reports\ReportByActivityAreaCityResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaCityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportByActivityAreaCity extends EditRecord
{
    protected static string $resource = ReportByActivityAreaCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
