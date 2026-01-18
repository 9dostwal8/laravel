<?php

namespace App\Filament\Resources\Reports\ReportByCityCapitalResource\Pages;

use App\Filament\Resources\Reports\ReportByCityCapitalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByCityCapitals extends ListRecords
{
    protected static string $resource = ReportByCityCapitalResource::class;

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
