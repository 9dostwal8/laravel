<?php

namespace App\Filament\Widgets;

use App\Models\ActivityArea;
use App\Traits\LangSwitcher;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ProjectsPerActivityArea extends ChartWidget
{
    use LangSwitcher;

    public function getHeading(): string|Htmlable|null
    {
        return trans('widgets.projects_per_activity_area');
    }

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $states = ActivityArea::query()
            ->withCount('projects')
            ->get();

        $data = [];
        foreach ($states as $state) {
            $data[self::getTranslation($state->name)] = $state->projects_count;
          //  $data[$state->{self::getModelName()}] = $state->projects_count;
        }

        return [
            'datasets' => [
                [
                    'label' => trans('widgets.projects_per_state'),
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 159, 64)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 77, 77)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 205, 86)',
                        'rgb(54, 162, 235)',
                        'rgb(128, 0, 128)',
                        'rgb(0, 128, 128)',
                        'rgb(255, 140, 0)',
                        'rgb(0, 128, 0)',
                        'rgb(255, 99, 71)',
                        'rgb(30, 144, 255)',
                        'rgb(255, 0, 0)',
                        'rgb(0, 255, 0)',
                        'rgb(255, 255, 0)',
                        'rgb(0, 0, 255)',
                    ],

                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
