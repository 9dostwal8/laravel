<?php

namespace App\Services;

use Filament\Forms\Components\TextInput;

class TranslatableField
{
    public static function make(
        string $name = 'name',
        $ckb = true,
        $ar = false,
        $en = false,
        $ckbHelperText = '',
        $arHelperText = '',
        $enHelperText = '',
    ): array
    {
        return [
            TextInput::make("$name.ckb")
                ->label(trans('resources.locale.kurdish'))
                ->required($ckb)
                ->helperText($ckbHelperText)
                ->maxLength(255),
            TextInput::make("$name.ar")
                ->label(trans('resources.locale.arabic'))
                ->required($ar)
                ->helperText($arHelperText)
                ->maxLength(255),
            TextInput::make("$name.en")
                ->label(trans('resources.locale.english'))
                ->required($en)
                ->helperText($enHelperText)
                ->maxLength(255),
        ];
    }
}
