<?php

namespace App\Filament\Resources\Reports\ReportByWorkforceCityResource\Pages;

use App\Filament\Resources\Reports\ReportByWorkforceCityResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListWorkforceCity extends ListRecords
{    
    protected static string $resource = ReportByWorkforceCityResource::class;
    
    public function getHeader(): ?View
    {
        return \view('reports.header');
    }

} 