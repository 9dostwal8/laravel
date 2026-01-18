<?php

namespace App\Filament\Resources\Reports\ReportByActivityAreaByJointResource\Pages;

use App\Filament\Resources\Reports\ReportByActivityAreaByJointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportByActivityAreaByJoint extends EditRecord
{
    protected static string $resource = ReportByActivityAreaByJointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
