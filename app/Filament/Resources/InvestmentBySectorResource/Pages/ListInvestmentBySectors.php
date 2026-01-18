<?php

namespace App\Filament\Resources\InvestmentBySectorResource\Pages;

use App\Filament\Resources\InvestmentBySectorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class ListInvestmentBySectors extends ListRecords
{
    protected static string $resource = InvestmentBySectorResource::class;

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
