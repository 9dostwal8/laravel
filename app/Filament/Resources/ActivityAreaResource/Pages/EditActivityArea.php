<?php

namespace App\Filament\Resources\ActivityAreaResource\Pages;

use App\Filament\Resources\ActivityAreaResource;
use Filament\Resources\Pages\EditRecord;

class EditActivityArea extends EditRecord
{
    protected static string $resource = ActivityAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
