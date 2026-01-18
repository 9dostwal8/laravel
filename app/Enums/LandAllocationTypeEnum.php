<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LandAllocationTypeEnum: int implements HasLabel
{
    case CHILDHOOD = 3;
    case OWNERSHIP = 2;
    case CONNECTIVITY = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONNECTIVITY => trans('land_allocation_type_enum.connectivity'),
            self::OWNERSHIP => trans('land_allocation_type_enum.ownership'),
            self::CHILDHOOD => trans('land_allocation_type_enum.childhood'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            LandAllocationTypeEnum::CONNECTIVITY => 'primary',
            LandAllocationTypeEnum::OWNERSHIP => 'info',
            LandAllocationTypeEnum::CHILDHOOD => 'success',
        };
    }
}
