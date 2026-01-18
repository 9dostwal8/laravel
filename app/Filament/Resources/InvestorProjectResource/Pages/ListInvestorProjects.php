<?php

namespace App\Filament\Resources\InvestorProjectResource\Pages;

use App\Filament\Resources\InvestorProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvestorProjects extends ListRecords
{
    protected static string $resource = InvestorProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
