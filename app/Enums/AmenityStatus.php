<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AmenityStatus: int implements HasLabel
{
    case STOPPED = 3;
    case IN_PROGRESS = 1;
    case READY = 2;
    case NOT_STARTED = 4;


    public function getLabel(): ?string
    {
        return match ($this) {
            AmenityStatus::IN_PROGRESS => trans('amenity_status_enum.in_progress'),
            AmenityStatus::READY => trans('amenity_status_enum.ready'),
            AmenityStatus::STOPPED => trans('amenity_status_enum.stopped'),
            AmenityStatus::NOT_STARTED => trans('amenity_status_enum.not_started'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            AmenityStatus::IN_PROGRESS => 'primary',
            AmenityStatus::READY => 'info',
            AmenityStatus::STOPPED => 'danger',
            AmenityStatus::NOT_STARTED => 'warning',
        };
    }
}
