<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LetterTypeEnum: int implements HasLabel
{
    case ISSUED = 1;
    case ARRIVED = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ISSUED => trans('letter_type_enum.issued'),
            self::ARRIVED => trans('letter_type_enum.arrived'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            LetterTypeEnum::ISSUED => 'primary',
            LetterTypeEnum::ARRIVED => 'success',
        };
    }
}
