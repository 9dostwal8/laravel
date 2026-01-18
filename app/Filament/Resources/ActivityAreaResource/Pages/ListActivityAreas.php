<?php

namespace App\Filament\Resources\ActivityAreaResource\Pages;

use App\Filament\Resources\ActivityAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivityAreas extends ListRecords
{
    protected static string $resource = ActivityAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
