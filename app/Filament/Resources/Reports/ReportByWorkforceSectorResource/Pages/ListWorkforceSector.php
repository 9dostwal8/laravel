<?php

namespace App\Filament\Resources\Reports\ReportByWorkforceSectorResource\Pages;

use App\Filament\Resources\Reports\ReportByWorkforceSectorResource;
use App\Traits\ReportsQuery;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListWorkforceSector extends ListRecords
{
    protected static string $resource = ReportByWorkforceSectorResource::class;

   
    public function getHeader(): ?View
    {
        return \view('reports.header');
    }

    public function getTitle(): string
    {
        return trans('resources.workforce_sector');
    }
} 