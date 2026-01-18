<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GenderEnum: int implements HasLabel
{
    case MALE = 1;
    case FEMALE = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => trans('gender_enum.male'),
            self::FEMALE => trans('gender_enum.female'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            GenderEnum::MALE => 'primary',
            GenderEnum::FEMALE => 'success',
        };
    }
}
