<?php

namespace App\Filament\Resources\InvestorProjectResource\Pages;

use App\Filament\Resources\InvestorProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvestorProject extends EditRecord
{
    protected static string $resource = InvestorProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
