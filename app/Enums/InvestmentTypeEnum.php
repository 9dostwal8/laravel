<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvestmentTypeEnum: int implements HasLabel
{
    case NATIONAL = 1;
    case FOREIGN = 2;
   // case JOINT_VENTURE = 3;
    case JOINT = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NATIONAL => trans('investment_type_enum.national'),
            self::FOREIGN => trans('investment_type_enum.foreign'),
          //  self::JOINT_VENTURE => trans('investment_type_enum.joint_nature'),
            self::JOINT => trans('investment_type_enum.joint'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            InvestmentTypeEnum::NATIONAL => 'primary',
            InvestmentTypeEnum::FOREIGN => 'info',
          //  InvestmentTypeEnum::JOINT_VENTURE => 'danger',
            InvestmentTypeEnum::JOINT => 'warning',
        };
    }
}
