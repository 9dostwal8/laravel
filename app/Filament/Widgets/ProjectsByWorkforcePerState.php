<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use App\Models\State;
use App\Traits\LangSwitcher;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ProjectsByWorkforcePerState extends ChartWidget
{
    use LangSwitcher;

    public function getHeading(): string|Htmlable|null
    {
        return trans('widgets.workforces_per_states');
    }

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $states = Organization::query()
            ->with('projects')
            ->get();

        $fixed = [];
        foreach ($states as $state) {
            $fixed[self::getTranslation($state->name)] = $state->projects->sum('kurdistan_fixed_workforce_count');
        }

        $temporary = [];
        foreach ($states as $state) {
            $temporary[self::getTranslation($state->name)] = $state->projects->sum('kurdistan_temporary_workforce_count');
        }

        return [
            'datasets' => [
                [
                    'label' => trans('widgets.fixed_workforces_number'),
                    'data' => array_values($fixed),
                    'fill' => true,
                    'backgroundColor' => 'rgba(0, 0, 255, 0.2)',
                    'borderColor' => 'rgb(0, 0, 255)',
                    'pointBackgroundColor' => 'rgb(0, 0, 255)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(0, 0, 255)',
                ],                [
                    'label' => trans('widgets.temporary_workforces_number'),
                    'data' => array_values($temporary),
                    'fill' => true,
                    'backgroundColor' => 'rgba(0, 255, 0, 0.2)',
                    'borderColor' => 'rgb(0, 255, 0)',
                    'pointBackgroundColor' => 'rgb(0, 255, 0)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(0, 255, 0)',
                ],
            ],
            'labels' => array_keys($fixed),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
