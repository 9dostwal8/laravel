<?php

namespace App\Filament\Resources\Reports\ReportByActivityAreaByJointResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaByJointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListReportByActivityAreaByJoints extends ListRecords
{
    protected static string $resource = ReportByActivityAreaByJointResource::class;

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
