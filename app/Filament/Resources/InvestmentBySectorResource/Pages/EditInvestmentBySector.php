<?php

namespace App\Filament\Resources\InvestmentBySectorResource\Pages;

use App\Filament\Resources\InvestmentBySectorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvestmentBySector extends EditRecord
{
    protected static string $resource = InvestmentBySectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
