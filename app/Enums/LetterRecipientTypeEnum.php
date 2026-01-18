<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LetterRecipientTypeEnum: int implements HasLabel
{
    case SENDER = 1;
    case RECEIVER = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SENDER => trans('letter_recipient_type_enum.sender'),
            self::RECEIVER => trans('letter_recipient_type_enum.receiver'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            LetterRecipientTypeEnum::SENDER => 'primary',
            LetterRecipientTypeEnum::RECEIVER => 'success',
        };
    }
}
