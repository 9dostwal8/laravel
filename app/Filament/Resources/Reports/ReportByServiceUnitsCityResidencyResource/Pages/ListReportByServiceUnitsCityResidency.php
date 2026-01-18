<?php

namespace App\Filament\Resources\Reports\ReportByServiceUnitsCityResidencyResource\Pages;

use App\Filament\Resources\Reports\ReportByServiceUnitsCityResidencyResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByServiceUnitsCityResidency extends ListRecords
{
    protected static string $resource = ReportByServiceUnitsCityResidencyResource::class;

    public function getHeader(): ?View
    {
        return \view('reports.header');
    }
} 