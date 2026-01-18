<?php

namespace App\Filament\Resources\Reports\ReportByInvestmentTypeResource\Pages;

use App\Filament\Resources\Reports\ReportByInvestmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByInvestmentType extends ListRecords
{
    protected static string $resource = ReportByInvestmentTypeResource::class;

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
