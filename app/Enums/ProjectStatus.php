<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: int implements HasLabel
{
    case IN_PROGRESS = 1;
    case TRADING = 8;
    //case ACCEPTED = 2;
    //case REJECTED = 3;
    //case SUSPENDED = 4;
    case LICENSED = 7;
    case CANCELED = 5;
    //case FINISHED = 6;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IN_PROGRESS => trans('project_status_enum.in_progress'),
           // self::ACCEPTED => trans('project_status_enum.accepted'),
           // self::REJECTED => trans('project_status_enum.rejected'),
            //self::SUSPENDED => trans('project_status_enum.suspended'),
            self::CANCELED => trans('project_status_enum.canceled'),
           // self::FINISHED => trans('project_status_enum.finished'),
            self::LICENSED => trans('project_status_enum.licensed'),

            self::TRADING => trans('project_status_enum.trading'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            ProjectStatus::IN_PROGRESS => 'primary',
          //  ProjectStatus::ACCEPTED => 'info',
          //  ProjectStatus::REJECTED => 'danger',
          //  ProjectStatus::SUSPENDED => 'warning',
            ProjectStatus::CANCELED => 'gray',
          //  ProjectStatus::FINISHED,
            ProjectStatus::LICENSED => 'success',
            ProjectStatus::TRADING => 'info',
        };
    }
}
