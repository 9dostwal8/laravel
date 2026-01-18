<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentTypeEnum: int implements HasLabel
{
    case INVESTMENT_BOARD_LICENSE = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INVESTMENT_BOARD_LICENSE => trans('document_type_enum.investment-board-license'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            DocumentTypeEnum::INVESTMENT_BOARD_LICENSE => 'success',
        };
    }
}
