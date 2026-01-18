<?php

namespace App\Filament\Resources\Reports\ReportByActivityAreaYearResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaYearResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByActivityAreaYear extends ListRecords
{
    protected static string $resource = ReportByActivityAreaYearResource::class;

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
