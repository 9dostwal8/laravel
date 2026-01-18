<?php

namespace App\Filament\Resources\Reports\ReportByProjectUnitStatusCityResidencyResource\Pages;

use App\Filament\Resources\Reports\ReportByProjectUnitStatusCityResidencyResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;

class ListReportByProjectUnitStatusCityResidency extends ListRecords
{
    protected static string $resource = ReportByProjectUnitStatusCityResidencyResource::class;
  
    public function getHeader(): ?View
    {
        return \view('reports.header');
    }
   
} 