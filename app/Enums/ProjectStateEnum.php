<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectStateEnum: int implements HasLabel
{
    case ITS_DONE = 1;
    case NOT_STARTED = 2;
    case LAND_PROBLEMS = 3;
    case PROJECT_UNITS_COMPLETED = 4;
    case REMAINING_BUSINESS_PERFORMANCE = 5;
    case INCOMPLETE_STOPPED_WORKING = 6;
    case NOT_FINISHED_WORKING = 7;
    case UNDER_TRANSACTION = 8;
    case LEGAL_PERIOD_REMAINS = 9;
    case NEEDS_VISIT_FOLLOW_UP = 10;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ITS_DONE => trans('project_state_enum.its-done'),
            self::NOT_STARTED => trans('project_state_enum.not-started'),
            self::LAND_PROBLEMS => trans('project_state_enum.land-problems'),
            self::PROJECT_UNITS_COMPLETED => trans('project_state_enum.project-units-completed'),
            self::REMAINING_BUSINESS_PERFORMANCE => trans('project_state_enum.remaining-business-performance'),
            self::INCOMPLETE_STOPPED_WORKING => trans('project_state_enum.incomplete-stopped-working'),
            self::NOT_FINISHED_WORKING => trans('project_state_enum.not-finished-working'),
            self::UNDER_TRANSACTION => trans('project_state_enum.under-transaction'),
            self::LEGAL_PERIOD_REMAINS => trans('project_state_enum.legal-period-remains'),
            self::NEEDS_VISIT_FOLLOW_UP => trans('project_state_enum.needs-visit-follow-up'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            ProjectStateEnum::NOT_STARTED => 'info',
            ProjectStateEnum::LAND_PROBLEMS => 'danger',
            ProjectStateEnum::REMAINING_BUSINESS_PERFORMANCE => 'gray',
            ProjectStateEnum::ITS_DONE, ProjectStateEnum::PROJECT_UNITS_COMPLETED => 'success',
            ProjectStateEnum::INCOMPLETE_STOPPED_WORKING, ProjectStateEnum::NOT_FINISHED_WORKING, ProjectStateEnum::UNDER_TRANSACTION, ProjectStateEnum::LEGAL_PERIOD_REMAINS, ProjectStateEnum::NEEDS_VISIT_FOLLOW_UP => 'warning',
        };
    }
}
