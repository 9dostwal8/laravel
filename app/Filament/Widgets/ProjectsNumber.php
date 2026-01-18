<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ProjectsNumber extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return trans('widgets.projects_progress');
    }

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $projects = Project::query()
            ->select('id')
            ->with('lastProgress')
            ->get();

        $ranges = [
            '26-50', '51-75', '76-100',
        ];

        $new = collect(['0-25' => $projects->count()]);

        foreach ($ranges as $range) {
            $arr = explode('-', $range);

            $count = $projects->where('lastProgress.progress_percentage', '>=', $arr[0])->count();

            $new->put($range, $count);
        }

        return [
            'datasets' => [
                [
                    'label' => trans('widgets.projects'),
                    'data' => $new->values(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                    ],
                ],
            ],
            'labels' => $new->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
