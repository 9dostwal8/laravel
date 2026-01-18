<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InspectorTypeEnum: int implements HasLabel
{
    case LEADER = 1;
    case MEMBER = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LEADER => trans('inspector_type_enum.leader'),
            self::MEMBER => trans('inspector_type_enum.member'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            InspectorTypeEnum::LEADER => 'primary',
            InspectorTypeEnum::MEMBER => 'success',
        };
    }
}
