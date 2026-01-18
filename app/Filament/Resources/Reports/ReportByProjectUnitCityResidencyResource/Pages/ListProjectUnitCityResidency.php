<?php

namespace App\Filament\Resources\Reports\ReportByProjectUnitCityResidencyResource\Pages;

use App\Filament\Resources\Reports\ReportByProjectUnitCityResidencyResource;
use App\Traits\LangSwitcher;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListProjectUnitCityResidency extends ListRecords
{
    use LangSwitcher;

    protected static string $resource = ReportByProjectUnitCityResidencyResource::class;

    public function getHeader(): ?View
    {
        return \view('reports.header');
    }
} 