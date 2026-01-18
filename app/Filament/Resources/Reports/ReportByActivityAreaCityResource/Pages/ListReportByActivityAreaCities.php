<?php

namespace App\Filament\Resources\Reports\ReportByActivityAreaCityResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaCityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByActivityAreaCities extends ListRecords
{
    protected static string $resource = ReportByActivityAreaCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        return \view('reports.header');
    }
}
