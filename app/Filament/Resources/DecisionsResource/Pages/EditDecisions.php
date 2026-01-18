<?php

namespace App\Filament\Resources\DecisionsResource\Pages;

use App\Filament\Resources\DecisionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDecisions extends EditRecord
{
    protected static string $resource = DecisionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
