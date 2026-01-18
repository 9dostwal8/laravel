<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectActivityAreaTypeEnum: int implements HasLabel
{
    case SINGLE = 1;
    case PARTNER = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            ProjectActivityAreaTypeEnum::SINGLE => trans('project_activity_area_type_enum.single'),
            ProjectActivityAreaTypeEnum::PARTNER => trans('project_activity_area_type_enum.partner'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            ProjectActivityAreaTypeEnum::SINGLE => 'primary',
            ProjectActivityAreaTypeEnum::PARTNER => 'info',
        };
    }
}
