<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                trans('project_status_enum.in_progress'),
                Project::ignoreOrganizationForSuperAdmin()
                    ->where('show', 1)
                    ->whereStatus(ProjectStatus::IN_PROGRESS)
                    ->count()
            ),
            Stat::make(
                trans('project_status_enum.trading'),
                Project::ignoreOrganizationForSuperAdmin()
                    ->where('show', 1)
                    ->whereStatus(ProjectStatus::TRADING)
                    ->count()
            ),
            Stat::make(
                trans('project_status_enum.licensed'),
                Project::ignoreOrganizationForSuperAdmin()
                    ->where('show', 1)
                    ->whereStatus(ProjectStatus::LICENSED)
                    ->count()
            ),
            Stat::make(
                trans('project_status_enum.canceled'),
                Project::ignoreOrganizationForSuperAdmin()
                    ->where('show', 1)
                    ->whereStatus(ProjectStatus::CANCELED)
                    ->count()
            ),

        ];
    }
}
