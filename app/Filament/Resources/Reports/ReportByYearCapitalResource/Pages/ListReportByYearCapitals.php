<?php

namespace App\Filament\Resources\Reports\ReportByYearCapitalResource\Pages;

use App\Filament\Resources\Reports\ReportByYearCapitalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByYearCapitals extends ListRecords
{
    protected static string $resource = ReportByYearCapitalResource::class;

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
