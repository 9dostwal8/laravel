<?php

namespace App\Filament\Widgets;

use App\Enums\InvestmentTypeEnum;
use App\Models\Project;
use App\Traits\LangSwitcher;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class ProjectsPerInvestmentType extends ChartWidget
{
    use LangSwitcher;

    public function getHeading(): string|Htmlable|null
    {
        return trans('widgets.projects_per_investment_type');
    }

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $projects = Project::query()
            ->select(['investment_type', DB::raw('COUNT(*) as type_count')])
            ->groupBy('investment_type')
            ->get();

        $data = [];
        foreach ($projects as $project) {
            $data[InvestmentTypeEnum::from($project->investment_type->value)->getLabel()] = $project->type_count;
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
                    ],

                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
