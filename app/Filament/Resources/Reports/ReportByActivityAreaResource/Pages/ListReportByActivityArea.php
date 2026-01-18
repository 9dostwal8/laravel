<?php

namespace App\Filament\Resources\Reports\ReportByCityCapitalResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByActivityArea extends ListRecords
{
    protected static string $resource = ReportByActivityAreaResource::class;

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
