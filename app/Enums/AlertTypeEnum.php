<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AlertTypeEnum: int implements HasLabel
{
    case VIOLATION = 1;
    case WARNING = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VIOLATION => trans('alert_type_enum.violation'),
            self::WARNING => trans('alert_type_enum.warning'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            AlertTypeEnum::VIOLATION => 'primary',
            AlertTypeEnum::WARNING => 'warning',
        };
    }
}
